@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">My P2P Ads</h1>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Create Ad</h2>
        <form method="POST" action="{{ route('app.p2p.ads.store') }}" class="grid gap-3 sm:grid-cols-3">
            @csrf
            <select name="side" class="input-field"><option value="sell">I am selling (buyer picks this ad)</option><option value="buy">I am buying (seller picks this ad)</option></select>
            <select name="asset_id" class="input-field">
                @foreach ($assets as $asset)<option value="{{ $asset->id }}">{{ $asset->symbol }}</option>@endforeach
            </select>
            <input type="text" name="fiat_currency" class="input-field" placeholder="Fiat currency (USD, NGN...)" required>
            <select name="price_type" class="input-field"><option value="fixed">Fixed price</option><option value="floating">Floating price</option></select>
            <input type="number" step="0.0001" name="price" class="input-field" placeholder="Price per unit" required>
            <input type="number" step="0.01" name="min_limit" class="input-field" placeholder="Min limit (fiat)" required>
            <input type="number" step="0.01" name="max_limit" class="input-field" placeholder="Max limit (fiat)" required>
            <input type="number" step="0.00000001" name="available_amount" class="input-field" placeholder="Available amount" required>
            <input type="text" name="region" class="input-field" placeholder="Region (Global, EU...)">
            <textarea name="terms" class="input-field sm:col-span-3" placeholder="Terms shown to counterparty" rows="2"></textarea>
            <input type="text" name="auto_reply" class="input-field sm:col-span-3" placeholder="Auto-reply message">
            <button class="btn-brand sm:col-span-3">Publish Ad</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Side</th><th>Asset</th><th>Price</th><th>Available</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($ads as $ad)
                    <tr>
                        <td>{{ ucfirst($ad->side) }}</td>
                        <td>{{ $ad->asset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($ad->price, 4) }} {{ $ad->fiat_currency }}</td>
                        <td class="font-numeric">{{ number_format($ad->available_amount, 6) }}</td>
                        <td><span class="pill-{{ $ad->status === 'active' ? 'success' : 'muted' }}">{{ $ad->status }}</span></td>
                        <td class="space-x-2">
                            <form method="POST" action="{{ route('app.p2p.ads.update', $ad) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $ad->status === 'active' ? 'paused' : 'active' }}">
                                <button class="text-xs text-brand hover:underline">{{ $ad->status === 'active' ? 'Pause' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">You have no ads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Payment Methods</h2>
        <form method="POST" action="{{ route('app.p2p.payment-methods.store') }}" class="mb-4 grid gap-3 sm:grid-cols-4">
            @csrf
            <input type="text" name="type" class="input-field" placeholder="Type (bank_transfer...)" required>
            <input type="text" name="account_name" class="input-field" placeholder="Account name" required>
            <input type="text" name="account_number" class="input-field" placeholder="Account number">
            <input type="text" name="bank_name" class="input-field" placeholder="Bank name">
            <button class="btn-outline sm:col-span-4">Add Payment Method</button>
        </form>
        @foreach ($paymentMethods as $pm)
            <div class="border-b border-border/60 py-2 text-sm">{{ $pm->type }} — {{ $pm->account_name }} {{ $pm->bank_name ? '('.$pm->bank_name.')' : '' }}</div>
        @endforeach
    </div>
</div>
@endsection
