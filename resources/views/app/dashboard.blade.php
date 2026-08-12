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
        <x-stat-card icon="dashboard" tone="brand" label="Total Portfolio Value" :value="'$'.number_format($portfolioTotal, 2)" sub="Across Primary, Trading &amp; Investment wallets" />
        <x-stat-card icon="wallet" tone="info" label="Primary Wallet" :value="'$'.number_format($walletTotals['primary'] ?? 0, 2)" href="{{ url('/app/wallet/primary') }}" sub="View wallet" />
        <x-stat-card icon="wallet" tone="info" label="Trading Wallet" :value="'$'.number_format($walletTotals['trading'] ?? 0, 2)" href="{{ url('/app/wallet/trading') }}" sub="View wallet" />
        <x-stat-card icon="wallet" tone="info" label="Investment Wallet" :value="'$'.number_format($walletTotals['investment'] ?? 0, 2)" href="{{ url('/app/wallet/investment') }}" sub="View wallet" />
    </div>

    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <x-stat-card icon="document" tone="muted" label="Open Orders" :value="$openOrders" />
        <x-stat-card icon="bolt" tone="muted" label="Futures Positions" :value="$openFutures" />
        <x-stat-card icon="cpu" tone="muted" label="Active AI Bots" :value="$activeBots" />
        <x-stat-card icon="cube" tone="muted" label="Mining Contracts" :value="$miningActive" />
        <x-stat-card icon="trending-up" :tone="$copyPnl >= 0 ? 'success' : 'danger'" label="Copy Trading P&amp;L" :value="'$'.number_format($copyPnl, 2)" />
        <x-stat-card icon="credit-card" tone="muted" label="Card Spend" :value="'$'.number_format($cardSpend, 2)" />
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="glass-card p-5 lg:col-span-2">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
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
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
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
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
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
