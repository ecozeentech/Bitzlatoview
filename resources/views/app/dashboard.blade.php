@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if (auth()->user()->kyc_status !== 'approved')
        <div class="risk-banner flex flex-wrap items-center justify-between gap-3">
            <div>
                <strong class="text-text-main">Identity verification: {{ str_replace('_', ' ', auth()->user()->kyc_status) }}.</strong>
                Complete KYC to unlock withdrawals, P2P merchant tools, cards, and futures/stocks/forex trading.
            </div>
            <a href="{{ url('/app/settings/kyc') }}" class="btn-brand text-sm">Start Verification</a>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="glass-card p-5">
            <p class="text-xs uppercase text-text-muted">Total Portfolio Value</p>
            <p class="mt-2 font-numeric text-2xl font-bold">${{ number_format($portfolioTotal, 2) }}</p>
            <p class="mt-1 text-xs text-text-muted">Across Primary, Trading &amp; Investment wallets</p>
        </div>
        <div class="glass-card p-5">
            <p class="text-xs uppercase text-text-muted">Primary Wallet</p>
            <p class="mt-2 font-numeric text-2xl font-bold">${{ number_format($walletTotals['primary'] ?? 0, 2) }}</p>
            <a href="{{ url('/app/wallet/primary') }}" class="mt-1 inline-block text-xs text-brand hover:underline">View wallet →</a>
        </div>
        <div class="glass-card p-5">
            <p class="text-xs uppercase text-text-muted">Trading Wallet</p>
            <p class="mt-2 font-numeric text-2xl font-bold">${{ number_format($walletTotals['trading'] ?? 0, 2) }}</p>
            <a href="{{ url('/app/wallet/trading') }}" class="mt-1 inline-block text-xs text-brand hover:underline">View wallet →</a>
        </div>
        <div class="glass-card p-5">
            <p class="text-xs uppercase text-text-muted">Investment Wallet</p>
            <p class="mt-2 font-numeric text-2xl font-bold">${{ number_format($walletTotals['investment'] ?? 0, 2) }}</p>
            <a href="{{ url('/app/wallet/investment') }}" class="mt-1 inline-block text-xs text-brand hover:underline">View wallet →</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="glass-card p-4 text-center">
            <p class="font-numeric text-xl font-bold">{{ $openOrders }}</p>
            <p class="text-xs text-text-muted">Open Orders</p>
        </div>
        <div class="glass-card p-4 text-center">
            <p class="font-numeric text-xl font-bold">{{ $openFutures }}</p>
            <p class="text-xs text-text-muted">Futures Positions</p>
        </div>
        <div class="glass-card p-4 text-center">
            <p class="font-numeric text-xl font-bold">{{ $activeBots }}</p>
            <p class="text-xs text-text-muted">Active AI Bots</p>
        </div>
        <div class="glass-card p-4 text-center">
            <p class="font-numeric text-xl font-bold">{{ $miningActive }}</p>
            <p class="text-xs text-text-muted">Mining Contracts</p>
        </div>
        <div class="glass-card p-4 text-center">
            <p class="font-numeric text-xl font-bold {{ $copyPnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($copyPnl, 2) }}</p>
            <p class="text-xs text-text-muted">Copy Trading P&amp;L</p>
        </div>
        <div class="glass-card p-4 text-center">
            <p class="font-numeric text-xl font-bold">${{ number_format($cardSpend, 2) }}</p>
            <p class="text-xs text-text-muted">Card Spend</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="glass-card p-5 lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Top Gainers (24h)</h2>
                <a href="/markets/top-gainers" class="text-xs text-brand hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                <thead><tr><th>Pair</th><th>Price</th><th>Change</th></tr></thead>
                <tbody>
                    @foreach ($topGainers as $market)
                        <tr>
                            <td class="flex items-center gap-2"><x-asset-icon :symbol="$market->baseAsset->symbol" /> {{ $market->symbol }}</td>
                            <td class="font-numeric">${{ number_format($market->quote->price ?? 0, 2) }}</td>
                            <td><x-price-change :value="$market->quote->change_24h_pct ?? 0" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div class="glass-card p-5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Watchlist</h2>
                <a href="{{ url('/app/markets') }}" class="text-xs text-brand hover:underline">Manage</a>
            </div>
            @forelse ($watchlist as $item)
                <div class="flex items-center justify-between border-b border-border/60 py-2 text-sm last:border-0">
                    <span class="flex items-center gap-2"><x-asset-icon :symbol="$item->marketPair->baseAsset->symbol" /> {{ $item->marketPair->symbol }}</span>
                    <x-price-change :value="$item->marketPair->quote->change_24h_pct ?? 0" />
                </div>
            @empty
                <p class="text-sm text-text-muted">No watchlist items yet. Star a market to add it here.</p>
            @endforelse
        </div>
    </div>

    <div class="glass-card p-5">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-semibold">Latest News</h2>
            <a href="{{ url('/app/news') }}" class="text-xs text-brand hover:underline">View all</a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($news as $article)
                <div class="rounded-xl border border-border p-3">
                    <span class="pill-{{ $article->sentiment === 'bullish' ? 'success' : ($article->sentiment === 'bearish' ? 'danger' : 'muted') }} mb-2">{{ ucfirst($article->sentiment) }}</span>
                    <p class="text-sm font-medium">{{ $article->title }}</p>
                    <p class="mt-1 text-xs text-text-muted">{{ $article->published_at?->diffForHumans() }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
