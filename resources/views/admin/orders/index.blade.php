@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Orders &amp; Trades</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Pair</th><th>Side</th><th>Type</th><th>Qty</th><th>Status</th><th>Fees</th></tr></thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr>
                        <td>{{ $o->user->email }}</td>
                        <td>{{ $o->marketPair->symbol }}</td>
                        <td class="{{ $o->side === 'buy' ? 'price-up' : 'price-down' }}">{{ ucfirst($o->side) }}</td>
                        <td>{{ ucfirst($o->type) }}</td>
                        <td class="font-numeric">{{ number_format($o->quantity, 6) }}</td>
                        <td><span class="pill-muted">{{ ucfirst($o->status) }}</span></td>
                        <td class="font-numeric text-text-muted">{{ number_format($o->trades->sum('fee'), 4) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-text-muted">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
@endsection
