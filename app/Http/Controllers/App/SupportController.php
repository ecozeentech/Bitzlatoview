<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())->latest()->get();

        return view('app.support.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:general,kyc,deposit,withdrawal,p2p,trading,card'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $data['subject'],
            'category' => $data['category'],
            'status' => 'open',
            'priority' => 'normal',
        ]);

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_admin' => false,
            'message' => $data['message'],
        ]);

        return redirect()->route('app.support.show', $ticket)->with('success', 'Support ticket created.');
    }

    public function show(SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);
        $ticket->load('messages.user');

        return view('app.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $data = $request->validate(['message' => ['required', 'string', 'max:3000']]);

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_admin' => false,
            'message' => $data['message'],
        ]);

        $ticket->update(['status' => 'pending']);

        return back()->with('success', 'Message sent.');
    }
}
