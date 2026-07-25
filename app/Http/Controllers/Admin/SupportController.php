<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with('user')->latest()->paginate(25);

        return view('admin.support.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load('messages.user', 'user');

        return view('admin.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:3000']]);

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'is_admin' => true,
            'message' => $data['message'],
        ]);

        $ticket->update(['status' => 'pending']);

        return back()->with('success', 'Reply sent.');
    }

    public function close(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'closed']);

        return back()->with('success', 'Ticket closed.');
    }
}
