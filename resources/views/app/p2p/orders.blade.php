@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">P2P Orders</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>#</th><th>Role</th><th>Asset</th><th>Amount</th><th>Fiat</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->buyer_id === auth()->id() ? 'Buyer' : 'Seller' }}</td>
                        <td>{{ $order->asset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($order->crypto_amount, 6) }}</td>
                        <td class="font-numeric">{{ number_format($order->fiat_amount, 2) }} {{ $order->fiat_currency }}</td>
                        <td><span class="pill-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ str_replace('_',' ',$order->status) }}</span></td>
                        <td><a href="{{ route('app.p2p.orders.show', $order) }}" class="text-sm text-brand hover:underline">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-text-muted">No P2P orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
