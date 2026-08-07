@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ addAsset: false, addPair: false, editFees: null }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Markets &amp; Assets</h1>
        <div class="flex gap-2">
            <button type="button" @click="addAsset = !addAsset" class="btn-outline text-sm">+ New Asset</button>
            <button type="button" @click="addPair = !addPair" class="btn-brand text-sm">+ New Pair</button>
        </div>
    </div>

    <div x-show="addAsset" x-cloak class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Create asset</h2>
        <form method="POST" action="{{ route('admin.assets.store') }}" class="grid gap-3 md:grid-cols-5">
            @csrf
            <input type="text" name="symbol" class="input-field" placeholder="Symbol (e.g. ADA)" required>
            <input type="text" name="name" class="input-field md:col-span-2" placeholder="Full name" required>
            <select name="type" class="input-field"><option value="crypto">Crypto</option><option value="fiat">Fiat</option><option value="stock">Stock</option></select>
            <input type="number" name="decimals" class="input-field" placeholder="Decimals" value="8" required>
            <button class="btn-brand text-sm md:col-span-5 w-fit">Create Asset</button>
        </form>
    </div>

    <div x-show="addPair" x-cloak class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Create trading pair</h2>
        <form method="POST" action="{{ route('admin.markets.store') }}" class="grid gap-3 md:grid-cols-5">
            @csrf
            <select name="base_asset_id" class="input-field" required>
                <option value="">Base asset</option>
                @foreach ($assets as $asset)<option value="{{ $asset->id }}">{{ $asset->symbol }}</option>@endforeach
            </select>
            <select name="quote_asset_id" class="input-field" required>
                <option value="">Quote asset</option>
                @foreach ($assets as $asset)<option value="{{ $asset->id }}">{{ $asset->symbol }}</option>@endforeach
            </select>
            <input type="number" step="0.0001" name="initial_price" class="input-field" placeholder="Initial price" required>
            <input type="number" step="0.0001" name="maker_fee_pct" class="input-field" placeholder="Maker fee %" value="0.1" required>
            <input type="number" step="0.0001" name="taker_fee_pct" class="input-field" placeholder="Taker fee %" value="0.1" required>
            <button class="btn-brand text-sm md:col-span-5 w-fit">Create Pair</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Pair</th><th>Price</th><th>24h %</th><th>Maker Fee</th><th>Taker Fee</th><th>Active</th><th></th></tr></thead>
            <tbody>
                @foreach ($pairs as $pair)
                    <tr>
                        <td>{{ $pair->symbol }}</td>
                        <td class="font-numeric">${{ number_format($pair->quote->price ?? 0, 4) }}</td>
                        <td><x-price-change :value="$pair->quote->change_24h_pct ?? 0" /></td>
                        <td>{{ $pair->maker_fee_pct }}%</td>
                        <td>{{ $pair->taker_fee_pct }}%</td>
                        <td><span class="pill-{{ $pair->is_active ? 'success' : 'muted' }}">{{ $pair->is_active ? 'Active' : 'Paused' }}</span></td>
                        <td class="space-y-1">
                            <form method="POST" action="{{ route('admin.markets.update', $pair) }}" class="flex items-center gap-1">
                                @csrf @method('PATCH')
                                <input type="number" step="0.0001" name="price" class="input-field w-24 text-xs" placeholder="Set price">
                                <input type="hidden" name="maker_fee_pct" value="{{ $pair->maker_fee_pct }}">
                                <input type="hidden" name="taker_fee_pct" value="{{ $pair->taker_fee_pct }}">
                                <input type="hidden" name="is_active" value="{{ $pair->is_active ? 1 : 0 }}">
                                <button class="btn-outline text-xs">Update Price</button>
                            </form>
                            <button type="button" @click="editFees = editFees === {{ $pair->id }} ? null : {{ $pair->id }}" class="text-xs text-brand hover:underline">Edit Fees</button>
                            <form method="POST" action="{{ route('admin.markets.update', $pair) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="maker_fee_pct" value="{{ $pair->maker_fee_pct }}">
                                <input type="hidden" name="taker_fee_pct" value="{{ $pair->taker_fee_pct }}">
                                <input type="hidden" name="is_active" value="{{ $pair->is_active ? 0 : 1 }}">
                                <button class="text-xs text-brand hover:underline">{{ $pair->is_active ? 'Pause' : 'Activate' }}</button>
                            </form>
                            <div x-show="editFees === {{ $pair->id }}" x-cloak>
                                <form method="POST" action="{{ route('admin.markets.update', $pair) }}" class="flex items-center gap-1">
                                    @csrf @method('PATCH')
                                    <input type="number" step="0.0001" name="maker_fee_pct" class="input-field w-20 text-xs" value="{{ $pair->maker_fee_pct }}" placeholder="Maker %">
                                    <input type="number" step="0.0001" name="taker_fee_pct" class="input-field w-20 text-xs" value="{{ $pair->taker_fee_pct }}" placeholder="Taker %">
                                    <input type="hidden" name="is_active" value="{{ $pair->is_active ? 1 : 0 }}">
                                    <button class="btn-outline text-xs">Save</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="glass-card overflow-x-auto">
        <h2 class="p-4 pb-0 font-semibold">Assets</h2>
        <table class="data-table">
            <thead><tr><th>Symbol</th><th>Name</th><th>Type</th><th>Active</th><th></th></tr></thead>
            <tbody>
                @foreach ($assets as $asset)
                    <tr>
                        <td>{{ $asset->symbol }}</td>
                        <td class="text-text-muted">{{ $asset->name }}</td>
                        <td>{{ ucfirst($asset->type) }}</td>
                        <td><span class="pill-{{ $asset->is_active ? 'success' : 'muted' }}">{{ $asset->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.assets.update', $asset) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="name" value="{{ $asset->name }}">
                                <input type="hidden" name="is_active" value="{{ $asset->is_active ? 0 : 1 }}">
                                <button class="text-xs text-brand hover:underline">{{ $asset->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
