@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Stock Trading (Paper Mode)</h1>
    <div class="risk-banner">This is a paper-trading environment. No real securities are bought or sold, and prices are not yet fed by a licensed market data vendor.</div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Symbol</th><th>Name</th><th>Price</th><th>Change</th><th></th></tr></thead>
            <tbody x-data="{ open: null }">
                @foreach ($instruments as $i)
                    <tr>
                        <td class="font-semibold">{{ $i->symbol }}</td>
                        <td class="text-text-muted">{{ $i->name }}</td>
                        <td class="font-numeric">${{ number_format($i->last_price, 2) }}</td>
                        <td><x-price-change :value="$i->change_pct" /></td>
                        <td><button type="button" @click="open = open === {{ $i->id }} ? null : {{ $i->id }}" class="btn-outline text-xs">Trade</button></td>
                    </tr>
                    <tr x-show="open === {{ $i->id }}" x-cloak>
                        <td colspan="5" class="bg-surface-2/40 p-3">
                            <form method="POST" action="{{ route('app.stocks.orders.store', $i) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <select name="side" class="input-field w-32"><option value="buy">Buy</option><option value="sell">Sell</option></select>
                                <input type="number" step="0.000001" name="quantity" class="input-field w-40" placeholder="Quantity" required>
                                <button class="btn-brand text-sm">Submit Order</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Portfolio Holdings</h2>
        <table class="data-table">
            <thead><tr><th>Symbol</th><th>Quantity</th><th>Avg Price</th><th>Market Value</th></tr></thead>
            <tbody>
                @forelse ($positions as $p)
                    <tr>
                        <td>{{ $p->instrument->symbol }}</td>
                        <td class="font-numeric">{{ number_format($p->quantity, 4) }}</td>
                        <td class="font-numeric text-text-muted">${{ number_format($p->avg_price, 2) }}</td>
                        <td class="font-numeric">${{ number_format($p->quantity * $p->instrument->last_price, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-text-muted">No holdings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Order History</h2>
        <table class="data-table">
            <thead><tr><th>Date</th><th>Symbol</th><th>Side</th><th>Qty</th><th>Price</th></tr></thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr>
                        <td class="text-text-muted">{{ $o->created_at->format('M d, H:i') }}</td>
                        <td>{{ $o->instrument->symbol }}</td>
                        <td class="{{ $o->side === 'buy' ? 'price-up' : 'price-down' }}">{{ ucfirst($o->side) }}</td>
                        <td class="font-numeric">{{ number_format($o->quantity, 4) }}</td>
                        <td class="font-numeric">${{ number_format($o->price, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
