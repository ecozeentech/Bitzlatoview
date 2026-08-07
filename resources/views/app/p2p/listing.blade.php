@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('app.p2p.buy') }}" class="{{ $mode === 'buy' ? 'nav-link-active' : 'nav-link' }}">Buy Crypto</a>
        <a href="{{ route('app.p2p.sell') }}" class="{{ $mode === 'sell' ? 'nav-link-active' : 'nav-link' }}">Sell Crypto</a>
        <a href="{{ route('app.p2p.orders') }}" class="nav-link">My Orders</a>
        <a href="{{ route('app.p2p.ads') }}" class="nav-link">My Ads</a>
        <a href="{{ route('app.p2p.merchant') }}" class="nav-link">Become a Merchant</a>
    </div>

    @if (auth()->user()->kyc_status !== 'approved')
        <div class="risk-banner">
            <strong class="text-text-main">Identity verification required.</strong> P2P trading involves real money moving between users, so we require approved KYC before you can open an order — this protects both sides of every trade. <a href="{{ url('/app/settings/kyc') }}" class="text-brand hover:underline">Complete verification</a> to unlock trading.
        </div>
    @endif

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Merchant</th><th>Price</th><th>Available</th><th>Limits</th><th>Payment</th><th></th></tr></thead>
            <tbody x-data="{ open: null }">
                @forelse ($ads as $ad)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-surface-2 text-xs font-bold">{{ substr($ad->user->name, 0, 1) }}</span>
                                <div>
                                    <p class="font-medium">{{ $ad->user->p2pMerchantProfile?->display_name ?? $ad->user->name }}
                                        @if ($ad->user->p2pMerchantProfile?->is_verified) <span class="pill-info">Verified</span> @endif
                                    </p>
                                    <p class="text-xs text-text-muted">{{ $ad->user->p2pMerchantProfile?->completed_orders ?? 0 }} orders · {{ $ad->user->p2pMerchantProfile?->completion_rate ?? 100 }}% completion</p>
                                </div>
                            </div>
                        </td>
                        <td class="font-numeric font-semibold">{{ number_format($ad->price, 4) }} {{ $ad->fiat_currency }}</td>
                        <td class="font-numeric">{{ number_format($ad->available_amount, 6) }} {{ $ad->asset->symbol }}</td>
                        <td class="text-text-muted">{{ number_format($ad->min_limit) }}–{{ number_format($ad->max_limit) }} {{ $ad->fiat_currency }}</td>
                        <td class="text-text-muted text-xs">{{ $ad->terms ? \Illuminate\Support\Str::limit($ad->terms, 40) : 'Any' }}</td>
                        <td>
                            @if (auth()->user()->kyc_status === 'approved')
                                <button type="button" @click="open = open === {{ $ad->id }} ? null : {{ $ad->id }}" class="btn-brand text-xs">{{ $mode === 'buy' ? 'Buy' : 'Sell' }} {{ $ad->asset->symbol }}</button>
                            @else
                                <a href="{{ url('/app/settings/kyc') }}" class="btn-outline text-xs">Verify to trade</a>
                            @endif
                        </td>
                    </tr>
                    <tr x-show="open === {{ $ad->id }}" x-cloak>
                        <td colspan="6" class="bg-surface-2/40">
                            <form method="POST" action="{{ route('app.p2p.orders.create') }}" class="flex flex-wrap items-end gap-3 p-3">
                                @csrf
                                <input type="hidden" name="ad_id" value="{{ $ad->id }}">
                                <div>
                                    <label class="label-field">Amount ({{ $ad->asset->symbol }})</label>
                                    <input type="number" step="0.00000001" name="crypto_amount" class="input-field w-40" required>
                                </div>
                                <div>
                                    <label class="label-field">Payment method</label>
                                    <input type="text" name="payment_method" class="input-field w-56" placeholder="Bank transfer, Revolut...">
                                </div>
                                <button class="btn-brand text-sm">Confirm — Lock Escrow</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No ads available right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="risk-banner">Only trade inside Bitzlatoview's escrow flow. Never send payment outside the platform or to unverified counterparties. Escrow protects the crypto side of the trade only — always confirm the fiat payment was actually received before releasing.</div>
</div>
@endsection
