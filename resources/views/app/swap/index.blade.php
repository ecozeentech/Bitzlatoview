@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <h1 class="text-2xl font-bold">Crypto Swap</h1>

    <div class="glass-card p-6" x-data="{
        amount: '', fromId: '', toId: '', quote: null, loading: false,
        fetchQuote() {
            if (!this.amount || !this.fromId || !this.toId || this.fromId === this.toId) { this.quote = null; return; }
            this.loading = true;
            fetch('{{ route('app.swap.quote') }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({ amount: this.amount, from_asset_id: this.fromId, to_asset_id: this.toId })
            }).then(r => r.json()).then(d => { this.quote = d; this.loading = false; }).catch(() => this.loading = false);
        }
    }">
        <form method="POST" action="{{ route('app.swap.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label-field">Wallet</label>
                <select name="wallet_type" class="input-field">
                    <option value="primary">Primary Wallet</option>
                    <option value="trading">Trading Wallet</option>
                    <option value="investment">Investment Wallet</option>
                </select>
            </div>
            <div>
                <label class="label-field">From</label>
                <select name="from_asset_id" x-model="fromId" @change="fetchQuote()" class="input-field">
                    <option value="">Select asset</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->symbol }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-field">Amount</label>
                <input type="number" step="0.00000001" name="amount" x-model="amount" @input.debounce.400ms="fetchQuote()" class="input-field" required>
            </div>
            <div>
                <label class="label-field">To</label>
                <select name="to_asset_id" x-model="toId" @change="fetchQuote()" class="input-field">
                    <option value="">Select asset</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->symbol }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-field">Slippage tolerance (%)</label>
                <input type="number" step="0.1" min="0.1" max="5" name="slippage_pct" value="0.5" class="input-field">
            </div>

            <div class="rounded-lg border border-border bg-surface-2 p-4 text-sm" x-show="quote" x-cloak>
                <div class="flex justify-between"><span class="text-text-muted">Rate</span><span class="font-numeric" x-text="quote ? quote.rate.toFixed(6) : ''"></span></div>
                <div class="flex justify-between"><span class="text-text-muted">Fee (0.25%)</span><span class="font-numeric" x-text="quote ? quote.fee.toFixed(8) : ''"></span></div>
                <div class="flex justify-between"><span class="text-text-muted">Minimum received</span><span class="font-numeric" x-text="quote ? quote.min_received.toFixed(8) : ''"></span></div>
                <div class="mt-1 flex justify-between font-semibold"><span>You receive ≈</span><span class="font-numeric text-brand" x-text="quote ? quote.net.toFixed(8) : ''"></span></div>
            </div>

            <div class="risk-banner">Swap rates track live market prices but settle internally on Bitzlatoview's ledger, not on-chain or against external exchange liquidity.</div>

            <button class="btn-brand w-full">Confirm Swap</button>
        </form>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Swap History</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Date</th><th>Wallet</th><th>From</th><th>To</th><th>Fee</th></tr></thead>
            <tbody>
                @forelse ($history as $h)
                    <tr>
                        <td class="text-text-muted">{{ $h->created_at->format('M d, H:i') }}</td>
                        <td>{{ ucfirst($h->walletAccount->type) }}</td>
                        <td class="font-numeric">{{ number_format($h->from_amount, 6) }} {{ $h->fromAsset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($h->to_amount, 6) }} {{ $h->toAsset->symbol }}</td>
                        <td class="font-numeric text-text-muted">{{ number_format($h->fee, 4) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No swaps yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
