<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminMessageController extends Controller
{
    public function index()
    {
        $messages = AdminMessage::with('user', 'sender')->latest()->take(100)->get();

        return view('admin.messages.index', compact('messages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'recipient' => ['required', 'in:all,single'],
            'user_email' => ['required_if:recipient,single', 'nullable', 'email', 'exists:users,email'],
        ]);

        $recipients = $data['recipient'] === 'all'
            ? User::where('role', 'user')->pluck('id')
            : User::where('email', $data['user_email'])->pluck('id');

        foreach ($recipients as $userId) {
            AdminMessage::create([
                'user_id' => $userId,
                'sent_by' => auth()->id(),
                'title' => $data['title'],
                'body' => $data['body'],
            ]);
        }

        AuditLog::record(auth()->user(), 'admin_message.sent', null, null, null, ['recipients' => $recipients->count(), 'title' => $data['title']]);

        return back()->with('success', "Message sent to {$recipients->count()} user(s).");
    }
}
