@extends('layouts.admin')

@section('content')
<div class="space-y-8" x-data="{ addStock: false, addForex: false, addFutures: false, editStock: null, editForex: null, editFutures: null }">
    <h1 class="text-2xl font-bold">Stocks / Forex / Futures Markets</h1>
    <div class="risk-banner">Stocks and forex remain in paper-trading mode platform-wide — no real securities or leveraged FX trades occur, and no broker-dealer/RFED registration is in place. Live broker adapters and a licensed market data feed would be required before removing this disclosure. Update prices manually below, via CSV import, or wire a licensed vendor API (e.g. Alpha Vantage, Twelve Data) using these same fields.</div>

    <div class="glass-card p-5 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold">Stocks</h2>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.stocks.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="file" name="csv" accept=".csv" class="input-field text-xs" required>
                    <button class="btn-outline text-xs">Import CSV</button>
                </form>
                <button type="button" @click="addStock = !addStock" class="btn-brand text-xs">+ Add Stock</button>
            </div>
        </div>
        <p class="text-xs text-text-muted">CSV columns: symbol, name, exchange, currency, last_price, change_pct</p>

        <form x-show="addStock" x-cloak method="POST" action="{{ route('admin.stocks.store') }}" class="grid gap-2 rounded-lg bg-surface-2/40 p-3 md:grid-cols-6">
            @csrf
            <input type="text" name="symbol" class="input-field" placeholder="Symbol" required>
            <input type="text" name="name" class="input-field md:col-span-2" placeholder="Company name" required>
            <input type="text" name="exchange" class="input-field" placeholder="Exchange" value="NASDAQ" required>
            <input type="text" name="currency" class="input-field" placeholder="Currency" value="USD" required>
            <input type="number" step="0.01" name="last_price" class="input-field" placeholder="Price" required>
            <button class="btn-brand text-xs md:col-span-6 w-fit">Create</button>
        </form>

        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Symbol</th><th>Name</th><th>Price</th><th>Change</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($stocks as $s)
                    <tr>
                        <td>{{ $s->symbol }}</td>
                        <td class="text-text-muted">{{ $s->name }}</td>
                        <td class="font-numeric">${{ number_format($s->last_price, 2) }}</td>
                        <td><x-price-change :value="$s->change_pct" /></td>
                        <td><span class="pill-{{ $s->is_active ? 'success' : 'muted' }}">{{ $s->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="flex gap-2">
                            <button type="button" @click="editStock = editStock === {{ $s->id }} ? null : {{ $s->id }}" class="text-xs text-brand hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.stocks.destroy', $s) }}" onsubmit="return confirm('Delete this instrument?')">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                        </td>
                    </tr>
                    <tr x-show="editStock === {{ $s->id }}" x-cloak>
                        <td colspan="6" class="bg-surface-2/40 p-3">
                            <form method="POST" action="{{ route('admin.stocks.update', $s) }}" class="grid gap-2 md:grid-cols-6">
                                @csrf @method('PUT')
                                <input type="text" name="name" class="input-field md:col-span-2" value="{{ $s->name }}" required>
                                <input type="text" name="exchange" class="input-field" value="{{ $s->exchange }}" required>
                                <input type="text" name="currency" class="input-field" value="{{ $s->currency }}" required>
                                <input type="number" step="0.01" name="last_price" class="input-field" value="{{ $s->last_price }}" required>
                                <input type="number" step="0.01" name="change_pct" class="input-field" value="{{ $s->change_pct }}" required>
                                <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_active" value="1" @checked($s->is_active)> Active</label>
                                <button class="btn-brand text-xs">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="glass-card p-5 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold">Forex Pairs</h2>
            <button type="button" @click="addForex = !addForex" class="btn-brand text-xs">+ Add Pair</button>
        </div>

        <form x-show="addForex" x-cloak method="POST" action="{{ route('admin.forex-pairs.store') }}" class="grid gap-2 rounded-lg bg-surface-2/40 p-3 md:grid-cols-6">
            @csrf
            <input type="text" name="base_currency" class="input-field" placeholder="Base (EUR)" required>
            <input type="text" name="quote_currency" class="input-field" placeholder="Quote (USD)" required>
            <input type="number" step="0.00001" name="bid" class="input-field" placeholder="Bid" required>
            <input type="number" step="0.00001" name="ask" class="input-field" placeholder="Ask" required>
            <input type="number" step="0.01" name="spread_pips" class="input-field" placeholder="Spread (pips)" required>
            <input type="number" name="leverage_max" class="input-field" placeholder="Max leverage" value="100" required>
            <button class="btn-brand text-xs md:col-span-6 w-fit">Create</button>
        </form>

        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Pair</th><th>Bid</th><th>Ask</th><th>Spread</th><th>Max Lev.</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($forexPairs as $p)
                    <tr>
                        <td>{{ $p->symbol }}</td>
                        <td class="font-numeric">{{ $p->bid }}</td>
                        <td class="font-numeric">{{ $p->ask }}</td>
                        <td class="text-text-muted">{{ $p->spread_pips }}</td>
                        <td>1:{{ $p->leverage_max }}</td>
                        <td><span class="pill-{{ $p->is_active ? 'success' : 'muted' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="flex gap-2">
                            <button type="button" @click="editForex = editForex === {{ $p->id }} ? null : {{ $p->id }}" class="text-xs text-brand hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.forex-pairs.destroy', $p) }}" onsubmit="return confirm('Delete this pair?')">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                        </td>
                    </tr>
                    <tr x-show="editForex === {{ $p->id }}" x-cloak>
                        <td colspan="7" class="bg-surface-2/40 p-3">
                            <form method="POST" action="{{ route('admin.forex-pairs.update', $p) }}" class="grid gap-2 md:grid-cols-6">
                                @csrf @method('PUT')
                                <input type="number" step="0.00001" name="bid" class="input-field" value="{{ $p->bid }}" required>
                                <input type="number" step="0.00001" name="ask" class="input-field" value="{{ $p->ask }}" required>
                                <input type="number" step="0.01" name="spread_pips" class="input-field" value="{{ $p->spread_pips }}" required>
                                <input type="number" name="leverage_max" class="input-field" value="{{ $p->leverage_max }}" required>
                                <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_active" value="1" @checked($p->is_active)> Active</label>
                                <button class="btn-brand text-xs">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="glass-card p-5 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold">Futures Markets</h2>
            <button type="button" @click="addFutures = !addFutures" class="btn-brand text-xs">+ Add Market</button>
        </div>

        <form x-show="addFutures" x-cloak method="POST" action="{{ route('admin.futures-markets.store') }}" class="grid gap-2 rounded-lg bg-surface-2/40 p-3 md:grid-cols-6">
            @csrf
            <input type="text" name="symbol" class="input-field" placeholder="Symbol (BTCUSDT-PERP)" required>
            <select name="asset_id" class="input-field" required>
                @foreach (\App\Models\Asset::orderBy('symbol')->get() as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->symbol }}</option>
                @endforeach
            </select>
            <input type="number" name="max_leverage" class="input-field" placeholder="Max leverage" value="20" required>
            <input type="number" step="0.0001" name="maintenance_margin_pct" class="input-field" placeholder="Maint. margin %" value="0.5" required>
            <input type="number" step="0.0001" name="funding_rate_pct" class="input-field" placeholder="Funding rate %" value="0.01" required>
            <button class="btn-brand text-xs">Create</button>
        </form>

        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Symbol</th><th>Mark Price</th><th>Max Leverage</th><th>Maint. Margin</th><th>Funding Rate</th><th></th></tr></thead>
            <tbody>
                @foreach ($futuresMarkets as $m)
                    <tr>
                        <td>{{ $m->symbol }}</td>
                        <td class="font-numeric">${{ number_format($m->mark_price, 2) }}</td>
                        <td>{{ $m->max_leverage }}x</td>
                        <td class="text-text-muted">{{ $m->maintenance_margin_pct }}%</td>
                        <td class="text-text-muted">{{ $m->funding_rate_pct }}%</td>
                        <td class="flex gap-2">
                            <button type="button" @click="editFutures = editFutures === {{ $m->id }} ? null : {{ $m->id }}" class="text-xs text-brand hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.futures-markets.destroy', $m) }}" onsubmit="return confirm('Delete this market?')">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                        </td>
                    </tr>
                    <tr x-show="editFutures === {{ $m->id }}" x-cloak>
                        <td colspan="6" class="bg-surface-2/40 p-3">
                            <form method="POST" action="{{ route('admin.futures-markets.update', $m) }}" class="grid gap-2 md:grid-cols-4">
                                @csrf @method('PUT')
                                <input type="number" name="max_leverage" class="input-field" value="{{ $m->max_leverage }}" required>
                                <input type="number" step="0.0001" name="maintenance_margin_pct" class="input-field" value="{{ $m->maintenance_margin_pct }}" required>
                                <input type="number" step="0.0001" name="funding_rate_pct" class="input-field" value="{{ $m->funding_rate_pct }}" required>
                                <button class="btn-brand text-xs">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <p class="text-xs text-text-muted">Mark price is synced automatically from live crypto prices (see <code>market:sync-prices</code>) via the underlying asset.</p>
    </div>
</div>
@endsection
