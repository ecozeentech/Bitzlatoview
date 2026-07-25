@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Buy / Sell Crypto</h1>
    <p class="text-sm text-text-muted">A beginner-friendly way to buy crypto using your USDT balance, or sell back to USDT. Fills instantly at simulated market pricing with a {{ 0.5 }}% fee.</p>

    <div class="glass-card p-6" x-data="{ side: 'buy' }">
        <div class="mb-4 flex rounded-lg bg-surface-2 p-1 text-sm">
            <button type="button" @click="side='buy'" :class="side==='buy' ? 'bg-brand-gradient text-background' : 'text-text-muted'" class="flex-1 rounded-md py-2 font-semibold">Buy</button>
            <button type="button" @click="side='sell'" :class="side==='sell' ? 'bg-brand-gradient text-background' : 'text-text-muted'" class="flex-1 rounded-md py-2 font-semibold">Sell</button>
        </div>

        <form method="POST" action="{{ route('app.buy-sell.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="side" :value="side">
            <div>
                <label class="label-field">Asset</label>
                <select name="asset_id" class="input-field">
                    @foreach ($cryptoAssets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->symbol }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-field" x-text="side === 'buy' ? 'Amount to spend (USDT)' : 'Amount to sell (crypto units)'"></label>
                <input type="number" step="0.00000001" name="amount" class="input-field" required>
            </div>
            <div class="risk-banner">Prices and fills are simulated. No real card/bank rails are connected in this build.</div>
            <button class="btn-brand w-full" x-text="side === 'buy' ? 'Buy Now' : 'Sell Now'"></button>
        </form>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Recent Buy/Sell History</h2>
        <table class="data-table">
            <thead><tr><th>Date</th><th>From</th><th>To</th><th>Rate</th><th>Fee</th></tr></thead>
            <tbody>
                @forelse ($history as $h)
                    <tr>
                        <td class="text-text-muted">{{ $h->created_at->format('M d, H:i') }}</td>
                        <td class="font-numeric">{{ number_format($h->from_amount, 6) }} {{ $h->fromAsset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($h->to_amount, 6) }} {{ $h->toAsset->symbol }}</td>
                        <td class="font-numeric text-text-muted">${{ number_format($h->rate, 2) }}</td>
                        <td class="font-numeric text-text-muted">{{ number_format($h->fee, 4) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
