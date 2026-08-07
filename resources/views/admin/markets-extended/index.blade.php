@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Stocks / Forex / Futures Markets</h1>
    <div class="risk-banner">Stocks and forex remain in paper-trading mode platform-wide. Live broker adapters (Alpaca, Tradier, DriveWealth, MT5 bridges) and a licensed market data feed are not connected yet — crypto markets already use live CoinGecko pricing.</div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Stocks</h2>
        <table class="data-table">
            <thead><tr><th>Symbol</th><th>Name</th><th>Price</th><th>Change</th></tr></thead>
            <tbody>@foreach ($stocks as $s)<tr><td>{{ $s->symbol }}</td><td class="text-text-muted">{{ $s->name }}</td><td class="font-numeric">${{ number_format($s->last_price,2) }}</td><td><x-price-change :value="$s->change_pct" /></td></tr>@endforeach</tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Forex Pairs</h2>
        <table class="data-table">
            <thead><tr><th>Pair</th><th>Bid</th><th>Ask</th><th>Spread</th></tr></thead>
            <tbody>@foreach ($forexPairs as $p)<tr><td>{{ $p->symbol }}</td><td class="font-numeric">{{ $p->bid }}</td><td class="font-numeric">{{ $p->ask }}</td><td class="text-text-muted">{{ $p->spread_pips }}</td></tr>@endforeach</tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Futures Markets</h2>
        <table class="data-table">
            <thead><tr><th>Symbol</th><th>Mark Price</th><th>Max Leverage</th><th>Funding Rate</th></tr></thead>
            <tbody>@foreach ($futuresMarkets as $m)<tr><td>{{ $m->symbol }}</td><td class="font-numeric">${{ number_format($m->mark_price,2) }}</td><td>{{ $m->max_leverage }}x</td><td class="text-text-muted">{{ $m->funding_rate_pct }}%</td></tr>@endforeach</tbody>
        </table>
    </div>
</div>
@endsection
