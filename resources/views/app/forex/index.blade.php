@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Forex Trading (Paper Mode)</h1>
    <div class="risk-banner">Forex trading carries a high level of risk and may not be suitable for all investors. Simulated pricing only. <a href="{{ url('/app/metatrader-5') }}" class="underline">Connect MetaTrader 5</a> for the broader Meta Trading experience.</div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Pair</th><th>Bid</th><th>Ask</th><th>Spread (pips)</th><th></th></tr></thead>
            <tbody x-data="{ open: null }">
                @foreach ($pairs as $p)
                    <tr>
                        <td class="font-semibold">{{ $p->symbol }}</td>
                        <td class="font-numeric price-down">{{ number_format($p->bid, 5) }}</td>
                        <td class="font-numeric price-up">{{ number_format($p->ask, 5) }}</td>
                        <td class="text-text-muted">{{ $p->spread_pips }}</td>
                        <td><button type="button" @click="open = open === {{ $p->id }} ? null : {{ $p->id }}" class="btn-outline text-xs">Trade</button></td>
                    </tr>
                    <tr x-show="open === {{ $p->id }}" x-cloak>
                        <td colspan="5" class="bg-surface-2/40 p-3">
                            <form method="POST" action="{{ route('app.forex.orders.store', $p) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <select name="side" class="input-field w-32"><option value="buy">Buy</option><option value="sell">Sell</option></select>
                                <input type="number" step="0.01" name="lot_size" class="input-field w-32" placeholder="Lot size" required>
                                <select name="leverage" class="input-field w-32">
                                    <option value="10">1:10</option><option value="20">1:20</option><option value="50" selected>1:50</option><option value="100">1:100</option>
                                </select>
                                <button class="btn-brand text-sm">Open Position</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Open Positions</h2>
        <table class="data-table">
            <thead><tr><th>Pair</th><th>Side</th><th>Lots</th><th>Leverage</th><th>Entry</th><th></th></tr></thead>
            <tbody>
                @forelse ($positions as $pos)
                    <tr>
                        <td>{{ $pos->pair->symbol }}</td>
                        <td class="{{ $pos->side === 'buy' ? 'price-up' : 'price-down' }}">{{ ucfirst($pos->side) }}</td>
                        <td class="font-numeric">{{ $pos->lot_size }}</td>
                        <td>1:{{ $pos->leverage }}</td>
                        <td class="font-numeric">{{ number_format($pos->entry_price, 5) }}</td>
                        <td><form method="POST" action="{{ route('app.forex.positions.close', $pos) }}">@csrf<button class="text-xs text-danger hover:underline">Close</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No open positions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
