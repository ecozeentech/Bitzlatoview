@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <select onchange="window.location = '/app/spot/' + this.value" class="input-field w-40">
                @foreach ($markets as $m)
                    <option value="{{ $m->symbol }}" @selected($m->symbol === $market->symbol)>{{ $m->symbol }}</option>
                @endforeach
            </select>
            <div>
                <p class="font-numeric text-2xl font-bold">${{ number_format($market->quote->price ?? 0, 4) }}</p>
                <x-price-change :value="$market->quote->change_24h_pct ?? 0" />
            </div>
        </div>
        <div class="flex gap-4 text-sm text-text-muted">
            <span>24h High: <span class="font-numeric text-text-main">${{ number_format($market->quote->high_24h ?? 0, 2) }}</span></span>
            <span>24h Low: <span class="font-numeric text-text-main">${{ number_format($market->quote->low_24h ?? 0, 2) }}</span></span>
            <span>24h Vol: <span class="font-numeric text-text-main">${{ number_format($market->quote->volume_24h ?? 0, 0) }}</span></span>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-4">
        <div class="glass-card flex h-80 items-center justify-center p-4 lg:col-span-3">
            <p class="text-sm text-text-muted">Candlestick chart placeholder — integrate TradingView Lightweight Charts here.</p>
        </div>
        <div class="glass-card p-4">
            <h3 class="mb-2 text-sm font-semibold">Recent Trades</h3>
            <table class="w-full text-xs">
                @forelse ($recentTrades as $t)
                    <tr>
                        <td class="py-0.5 font-numeric">${{ number_format($t->price, 2) }}</td>
                        <td class="py-0.5 font-numeric text-text-muted">{{ number_format($t->quantity, 4) }}</td>
                        <td class="py-0.5 text-text-muted">{{ $t->created_at->format('H:i:s') }}</td>
                    </tr>
                @empty
                    <tr><td class="text-text-muted">No trades yet.</td></tr>
                @endforelse
            </table>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="glass-card p-5">
            <h3 class="mb-3 font-semibold price-up">Buy {{ $market->baseAsset->symbol }}</h3>
            <form method="POST" action="{{ route('app.spot.orders.store', $market->symbol) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="side" value="buy">
                <div class="flex rounded-lg bg-surface-2 p-1 text-xs">
                    <label class="flex-1 cursor-pointer rounded-md px-2 py-1.5 text-center"><input type="radio" name="type" value="market" checked class="mr-1">Market</label>
                    <label class="flex-1 cursor-pointer rounded-md px-2 py-1.5 text-center"><input type="radio" name="type" value="limit" class="mr-1">Limit</label>
                </div>
                <div>
                    <label class="label-field">Price ({{ $market->quoteAsset->symbol }}) — leave blank for market</label>
                    <input type="number" step="0.0001" name="price" class="input-field">
                </div>
                <div>
                    <label class="label-field">Quantity ({{ $market->baseAsset->symbol }})</label>
                    <input type="number" step="0.00000001" name="quantity" class="input-field" required>
                </div>
                <p class="text-xs text-text-muted">Available: {{ number_format($quoteBalance->available, 2) }} {{ $market->quoteAsset->symbol }}</p>
                <button class="w-full rounded-lg bg-success px-4 py-2 font-semibold text-background hover:brightness-105">Buy {{ $market->baseAsset->symbol }}</button>
            </form>
        </div>

        <div class="glass-card p-5">
            <h3 class="mb-3 font-semibold price-down">Sell {{ $market->baseAsset->symbol }}</h3>
            <form method="POST" action="{{ route('app.spot.orders.store', $market->symbol) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="side" value="sell">
                <div class="flex rounded-lg bg-surface-2 p-1 text-xs">
                    <label class="flex-1 cursor-pointer rounded-md px-2 py-1.5 text-center"><input type="radio" name="type" value="market" checked class="mr-1">Market</label>
                    <label class="flex-1 cursor-pointer rounded-md px-2 py-1.5 text-center"><input type="radio" name="type" value="limit" class="mr-1">Limit</label>
                </div>
                <div>
                    <label class="label-field">Price ({{ $market->quoteAsset->symbol }}) — leave blank for market</label>
                    <input type="number" step="0.0001" name="price" class="input-field">
                </div>
                <div>
                    <label class="label-field">Quantity ({{ $market->baseAsset->symbol }})</label>
                    <input type="number" step="0.00000001" name="quantity" class="input-field" required>
                </div>
                <p class="text-xs text-text-muted">Available: {{ number_format($baseBalance->available, 8) }} {{ $market->baseAsset->symbol }}</p>
                <button class="w-full rounded-lg bg-danger px-4 py-2 font-semibold text-background hover:brightness-105">Sell {{ $market->baseAsset->symbol }}</button>
            </form>
        </div>

        <div class="glass-card p-5 text-sm text-text-muted">
            <h3 class="mb-3 font-semibold text-text-main">Order Book (simulated)</h3>
            <p>A full depth chart / order book is a UI placeholder in this build. Market orders fill instantly against simulated internal liquidity; limit orders fill immediately if the limit crosses the current price, otherwise they remain open until cancelled.</p>
            <p class="mt-3 risk-banner">Spot trading involves risk of loss. Prices in this build are simulated.</p>
        </div>
    </div>

    <div class="glass-card p-5">
        <h3 class="mb-3 font-semibold">Open Orders</h3>
        <table class="data-table">
            <thead><tr><th>Side</th><th>Type</th><th>Price</th><th>Qty</th><th>Filled</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($openOrders as $o)
                    <tr>
                        <td class="{{ $o->side === 'buy' ? 'price-up' : 'price-down' }}">{{ ucfirst($o->side) }}</td>
                        <td>{{ ucfirst($o->type) }}</td>
                        <td class="font-numeric">{{ $o->price ? number_format($o->price, 4) : 'Market' }}</td>
                        <td class="font-numeric">{{ number_format($o->quantity, 6) }}</td>
                        <td class="font-numeric text-text-muted">{{ number_format($o->filled_quantity, 6) }}</td>
                        <td><span class="pill-warning">{{ str_replace('_',' ',$o->status) }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('app.spot.orders.cancel', $o) }}">
                                @csrf
                                <button class="text-xs text-danger hover:underline">Cancel</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-text-muted">No open orders.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h3 class="mb-3 font-semibold">Order History</h3>
        <table class="data-table">
            <thead><tr><th>Side</th><th>Type</th><th>Price</th><th>Qty</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @forelse ($orderHistory as $o)
                    <tr>
                        <td class="{{ $o->side === 'buy' ? 'price-up' : 'price-down' }}">{{ ucfirst($o->side) }}</td>
                        <td>{{ ucfirst($o->type) }}</td>
                        <td class="font-numeric">{{ $o->price ? number_format($o->price, 4) : 'Market' }}</td>
                        <td class="font-numeric">{{ number_format($o->quantity, 6) }}</td>
                        <td><span class="pill-muted">{{ ucfirst($o->status) }}</span></td>
                        <td class="text-text-muted">{{ $o->created_at->format('M d, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No order history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
