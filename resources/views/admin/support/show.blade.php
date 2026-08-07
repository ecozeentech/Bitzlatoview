@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-bold">{{ $ticket->subject }} — {{ $ticket->user->email }}</h1>
        @if ($ticket->status !== 'closed')
            <form method="POST" action="{{ route('admin.support.close', $ticket) }}">@csrf<button class="btn-outline text-sm">Close Ticket</button></form>
        @endif
    </div>

    <div class="glass-card p-5">
        <div class="mb-3 max-h-96 space-y-2 overflow-y-auto">
            @foreach ($ticket->messages as $m)
                <div class="rounded-lg p-3 text-sm {{ $m->is_admin ? 'bg-brand/10' : 'bg-surface-2' }}">
                    <p class="text-xs text-text-muted">{{ $m->is_admin ? 'Support Agent' : $ticket->user->name }} · {{ $m->created_at->format('M d, H:i') }}</p>
                    <p>{{ $m->message }}</p>
                </div>
            @endforeach
        </div>
        <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="flex gap-2">
            @csrf
            <input type="text" name="message" class="input-field flex-1" placeholder="Reply..." required>
            <button class="btn-brand text-sm">Send</button>
        </form>
    </div>
</div>
@endsection
