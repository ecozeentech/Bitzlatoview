@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Futures Trading</h1>
    <div class="risk-banner">Futures trading is extremely high risk and can result in losses exceeding your deposit. Positions settle on Bitzlatoview's internal engine against real crypto market prices, not a live external exchange. Requires KYC and a separate futures risk agreement.</div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Market</th><th>Mark Price</th><th>Funding Rate</th><th>Max Leverage</th><th></th></tr></thead>
            <tbody x-data="{ open: null }">
                @foreach ($markets as $m)
                    <tr>
                        <td class="flex items-center gap-2"><x-asset-icon :symbol="$m->asset->symbol" /> {{ $m->symbol }}</td>
                        <td class="font-numeric">${{ number_format($m->mark_price, 2) }}</td>
                        <td class="font-numeric text-text-muted">{{ $m->funding_rate_pct }}%</td>
                        <td>{{ $m->max_leverage }}x</td>
                        <td><button type="button" @click="open = open === {{ $m->id }} ? null : {{ $m->id }}" class="btn-outline text-xs">Trade</button></td>
                    </tr>
                    <tr x-show="open === {{ $m->id }}" x-cloak>
                        <td colspan="5" class="bg-surface-2/40 p-3">
                            <form method="POST" action="{{ route('app.futures.positions.store', $m) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <select name="side" class="input-field w-32"><option value="long">Long</option><option value="short">Short</option></select>
                                <input type="number" step="0.0001" name="quantity" class="input-field w-32" placeholder="Quantity" required>
                                <select name="leverage" class="input-field w-32">
                                    @foreach ([2,5,10,20,min(50,$m->max_leverage)] as $lev)
                                        <option value="{{ $lev }}">{{ $lev }}x</option>
                                    @endforeach
                                </select>
                                <select name="margin_mode" class="input-field w-32"><option value="isolated">Isolated</option><option value="cross">Cross</option></select>
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
            <thead><tr><th>Market</th><th>Side</th><th>Qty</th><th>Leverage</th><th>Entry</th><th>Liq. Price</th><th>Margin</th><th></th></tr></thead>
            <tbody>
                @forelse ($positions as $p)
                    <tr>
                        <td>{{ $p->market->symbol }}</td>
                        <td class="{{ $p->side === 'long' ? 'price-up' : 'price-down' }}">{{ ucfirst($p->side) }}</td>
                        <td class="font-numeric">{{ number_format($p->quantity, 4) }}</td>
                        <td>{{ $p->leverage }}x {{ $p->margin_mode }}</td>
                        <td class="font-numeric">${{ number_format($p->entry_price, 2) }}</td>
                        <td class="font-numeric text-danger">${{ number_format($p->liquidation_price, 2) }}</td>
                        <td class="font-numeric">${{ number_format($p->margin, 2) }}</td>
                        <td><form method="POST" action="{{ route('app.futures.positions.close', $p) }}">@csrf<button class="text-xs text-danger hover:underline">Close</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-text-muted">No open positions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Position History</h2>
        <table class="data-table">
            <thead><tr><th>Market</th><th>Side</th><th>P&amp;L</th><th>Closed</th></tr></thead>
            <tbody>
                @forelse ($history as $p)
                    <tr>
                        <td>{{ $p->market->symbol }}</td>
                        <td class="{{ $p->side === 'long' ? 'price-up' : 'price-down' }}">{{ ucfirst($p->side) }}</td>
                        <td class="font-numeric {{ $p->pnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($p->pnl, 2) }}</td>
                        <td class="text-text-muted">{{ $p->closed_at?->format('M d, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-text-muted">No history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
