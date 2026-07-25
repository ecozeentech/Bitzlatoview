@extends('layouts.app')

@section('content')
@php $isBuyer = $order->buyer_id === auth()->id(); @endphp
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">P2P Order #{{ $order->id }}</h1>
        <span class="pill-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ str_replace('_',' ',$order->status) }}</span>
    </div>

    <div class="glass-card p-5">
        <div class="grid gap-3 sm:grid-cols-2 text-sm">
            <div><span class="text-text-muted">Role:</span> {{ $isBuyer ? 'Buyer' : 'Seller' }}</div>
            <div><span class="text-text-muted">Counterparty:</span> {{ $isBuyer ? $order->seller->name : $order->buyer->name }}</div>
            <div><span class="text-text-muted">Amount:</span> {{ number_format($order->crypto_amount, 8) }} {{ $order->asset->symbol }}</div>
            <div><span class="text-text-muted">Fiat total:</span> {{ number_format($order->fiat_amount, 2) }} {{ $order->fiat_currency }}</div>
            <div><span class="text-text-muted">Payment method:</span> {{ $order->payment_method ?? 'Not specified' }}</div>
            <div><span class="text-text-muted">Expires:</span> {{ $order->expires_at?->diffForHumans() }}</div>
        </div>

        <div class="risk-banner mt-4">Do not trade outside Bitzlatoview. Verify the payer/payee name matches the counterparty's verified name before releasing or paying.</div>

        <div class="mt-4 flex flex-wrap gap-2">
            @if ($isBuyer && in_array($order->status, ['escrow_locked', 'awaiting_payment']))
                <form method="POST" action="{{ route('app.p2p.orders.mark-paid', $order) }}">@csrf<button class="btn-brand text-sm">I Have Paid</button></form>
            @endif
            @if (! $isBuyer && in_array($order->status, ['paid', 'escrow_locked']))
                <form method="POST" action="{{ route('app.p2p.orders.release', $order) }}">@csrf<button class="btn-brand text-sm">Release Escrow</button></form>
            @endif
            @if (in_array($order->status, ['escrow_locked', 'awaiting_payment']))
                <form method="POST" action="{{ route('app.p2p.orders.cancel', $order) }}">@csrf<button class="btn-outline text-sm">Cancel Order</button></form>
            @endif
            @if (! $order->appeal && ! in_array($order->status, ['completed', 'cancelled']))
                <details class="inline-block">
                    <summary class="btn-outline cursor-pointer text-sm">Open Appeal</summary>
                    <form method="POST" action="{{ route('app.p2p.orders.appeal', $order) }}" class="glass-card mt-2 space-y-2 p-3">
                        @csrf
                        <textarea name="reason" class="input-field" rows="3" placeholder="Describe the issue..." required></textarea>
                        <input type="url" name="evidence_url" class="input-field" placeholder="Evidence URL (optional)">
                        <button class="btn-brand text-sm">Submit Appeal</button>
                    </form>
                </details>
            @endif
        </div>
    </div>

    @if ($order->appeal)
        <div class="glass-card p-5">
            <h2 class="mb-2 font-semibold">Appeal</h2>
            <p class="text-sm text-text-muted">Status: <span class="pill-warning">{{ $order->appeal->status }}</span></p>
            <p class="mt-2 text-sm">{{ $order->appeal->reason }}</p>
            @if ($order->appeal->resolution)
                <p class="mt-2 text-sm text-success">Resolution: {{ $order->appeal->resolution }}</p>
            @endif
        </div>
    @endif

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Order Chat</h2>
        <div class="mb-3 max-h-72 space-y-2 overflow-y-auto">
            @forelse ($order->messages as $message)
                <div class="rounded-lg p-2 text-sm {{ $message->user_id === auth()->id() ? 'ml-auto bg-brand/10 text-right' : 'bg-surface-2' }} max-w-[75%]">
                    <p class="text-xs text-text-muted">{{ $message->user->name }} · {{ $message->created_at->format('H:i') }}</p>
                    <p>{{ $message->message }}</p>
                </div>
            @empty
                <p class="text-sm text-text-muted">No messages yet.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('app.p2p.orders.messages', $order) }}" class="flex gap-2">
            @csrf
            <input type="text" name="message" class="input-field flex-1" placeholder="Type a message..." required>
            <button class="btn-brand text-sm">Send</button>
        </form>
    </div>
</div>
@endsection
