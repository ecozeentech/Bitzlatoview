@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="dashboardPage()">
    @if (auth()->user()->kyc_status !== 'approved')
        <div class="risk-banner flex flex-wrap items-center justify-between gap-3">
            <div>
                <strong class="text-text-main">Identity verification: {{ str_replace('_', ' ', auth()->user()->kyc_status) }}.</strong>
                Complete KYC to unlock withdrawals, P2P merchant tools, cards, and futures/stocks/forex trading.
            </div>
            <a href="{{ url('/app/settings/kyc') }}" class="btn-brand text-sm">Start Verification</a>
        </div>
    @endif

    {{-- Balance & Quick Actions --}}
    <div class="glass-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-xs uppercase tracking-wide text-text-muted">Available Balance</p>
                    @php
                        $kycStyles = [
                            'approved' => ['success', 'Verified'],
                            'submitted' => ['warning', 'Pending Review'],
                            'rejected' => ['danger', 'Rejected'],
                            'not_started' => ['muted', 'Not Started'],
                        ];
                        [$kycTone, $kycLabel] = $kycStyles[auth()->user()->kyc_status] ?? ['muted', str_replace('_', ' ', auth()->user()->kyc_status)];
                    @endphp
                    <a href="{{ url('/app/settings/kyc') }}" class="pill-{{ $kycTone }}">KYC: {{ $kycLabel }}</a>
                </div>
                <p class="mt-1 font-numeric text-3xl font-bold">${{ number_format($portfolioTotal, 2) }}</p>
                <p class="font-numeric text-sm text-text-muted">{{ number_format($portfolioTotalBtc, 6) }} BTC</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ url('/app/funding/deposit') }}" class="btn-brand">Deposit</a>
                <a href="{{ url('/app/funding/withdraw') }}" class="btn-outline">Withdraw</a>
            </div>
        </div>

        @unless (auth()->user()->two_factor_enabled)
            <a href="{{ url('/app/settings/security') }}" class="mt-4 flex items-center gap-2 rounded-lg border border-brand/30 bg-brand/5 px-4 py-2.5 text-sm text-text-muted hover:border-brand/50">
                <svg class="h-4 w-4 shrink-0 text-brand" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                Secure your account by enabling 2FA for extra protection.
                <span class="ml-auto shrink-0 text-brand">Enable →</span>
            </a>
        @endunless

        {{-- Feature bar --}}
        <div class="mt-4 flex gap-2 overflow-x-auto border-t border-border pt-4">
            @foreach ([
                ['Spot', 'presentation', 'app/spot'],
                ['Futures', 'bolt', 'app/futures'],
                ['Stocks', 'building', 'app/stocks'],
                ['Copy Trade', 'trending-up', 'app/copy-trading'],
                ['Signals', 'bolt', 'app/signals'],
                ['AI Bots', 'cpu', 'app/ai-bots'],
                ['Mining', 'cube', 'app/mining'],
                ['Support', 'lifebuoy', 'app/support'],
            ] as [$label, $icon, $prefix])
                <a href="{{ url($prefix) }}" class="flex w-20 shrink-0 flex-col items-center gap-1.5 rounded-xl border border-border bg-surface-2/40 p-3 text-center text-[11px] text-text-muted transition hover:border-brand/40 hover:text-text-main">
                    <x-nav-icon :name="$icon" class="h-5 w-5" />
                    {{ $label }}
                </a>
            @endforeach
            <button type="button" @click="featuresOpen = true" class="flex w-20 shrink-0 flex-col items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/5 p-3 text-center text-[11px] text-brand transition hover:bg-brand/10">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                More
            </button>
        </div>
    </div>

    <x-features-popup />

    {{-- Secondary stats --}}
    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <x-stat-card icon="document" tone="muted" label="Open Orders" :value="$openOrders" />
        <x-stat-card icon="bolt" tone="muted" label="Futures Positions" :value="$openFutures" />
        <x-stat-card icon="cpu" tone="muted" label="Active AI Bots" :value="$activeBots" />
        <x-stat-card icon="cube" tone="muted" label="Mining Contracts" :value="$miningActive" />
        <x-stat-card icon="trending-up" :tone="$copyPnl >= 0 ? 'success' : 'danger'" label="Copy Trading P&amp;L" :value="'$'.number_format($copyPnl, 2)" />
        <x-stat-card icon="credit-card" tone="muted" label="Card Spend" :value="'$'.number_format($cardSpend, 2)" />
    </div>

    {{-- Insights / Market Movers --}}
    <div class="glass-card p-5">
        <div class="mb-1 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold">Market Movers</h2>
                <p class="text-xs text-text-muted">The largest 24h price swings right now — swipe to explore. Based on real live prices, not investment advice.</p>
            </div>
        </div>
        <div class="mt-3 flex gap-3 overflow-x-auto pb-2">
            @forelse ($movers as $m)
                @php
                    $chg = (float) ($m->quote?->change_24h_pct ?? 0);
                    $blurb = $chg > 5 ? 'Momentum is building fast.' : ($chg > 0 ? 'Trending upward over the last 24h.' : ($chg < -5 ? 'Sharp pullback in the last 24h.' : 'Cooling off over the last 24h.'));
                @endphp
                <a href="{{ url('/app/spot/'.$m->symbol) }}" class="glass-card w-56 shrink-0 p-4 transition hover:border-brand/30">
                    <div class="flex items-center gap-2">
                        <x-asset-icon :symbol="$m->baseAsset->symbol" />
                        <p class="font-semibold">{{ $m->baseAsset->symbol }}</p>
                        <x-price-change class="ml-auto" :value="$chg" />
                    </div>
                    <p class="mt-2 font-numeric text-lg font-bold">${{ number_format($m->quote?->price ?? 0, 2) }}</p>
                    <p class="mt-1 text-xs text-text-muted">{{ $blurb }}</p>
                </a>
            @empty
                <p class="text-sm text-text-muted">No market data yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Market Overview --}}
    <div class="glass-card p-5" x-data="{ overviewTab: 'crypto' }">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold">Market Overview</h2>
            <div class="flex gap-1 text-sm">
                <button type="button" @click="overviewTab = 'crypto'" :class="overviewTab === 'crypto' ? 'nav-link-active' : 'nav-link'">Cryptos</button>
                <button type="button" @click="overviewTab = 'stocks'" :class="overviewTab === 'stocks' ? 'nav-link-active' : 'nav-link'">Stocks</button>
                <button type="button" @click="overviewTab = 'nft'" :class="overviewTab === 'nft' ? 'nav-link-active' : 'nav-link'">NFT</button>
            </div>
        </div>

        <div x-show="overviewTab === 'crypto'">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Asset</th><th>Price</th><th>24h %</th></tr></thead>
                    <tbody>
                        @foreach ($topGainers as $m)
                            <tr>
                                <td class="flex items-center gap-2"><x-asset-icon :symbol="$m->baseAsset->symbol" /> {{ $m->symbol }}</td>
                                <td class="font-numeric">${{ number_format($m->quote->price ?? 0, 2) }}</td>
                                <td><x-price-change :value="$m->quote->change_24h_pct ?? 0" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <a href="{{ url('/app/markets') }}" class="mt-3 inline-block text-xs text-brand hover:underline">View All →</a>
        </div>

        <div x-show="overviewTab === 'stocks'" x-cloak>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Symbol</th><th>Name</th><th>Price</th><th>Change</th></tr></thead>
                    <tbody>
                        @forelse ($stockInstruments as $s)
                            <tr>
                                <td class="font-semibold">{{ $s->symbol }}</td>
                                <td class="text-text-muted">{{ $s->name }}</td>
                                <td class="font-numeric">${{ number_format($s->last_price, 2) }}</td>
                                <td><x-price-change :value="$s->change_pct" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-text-muted">No stock instruments listed yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ url('/app/stocks') }}" class="mt-3 inline-block text-xs text-brand hover:underline">View All →</a>
        </div>

        <div x-show="overviewTab === 'nft'" x-cloak>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($nftCollections as $c)
                    <a href="{{ route('app.nft.collections.show', $c) }}" class="rounded-xl border border-border p-3 hover:border-brand/40">
                        <p class="font-semibold">{{ $c->name }}</p>
                        <p class="text-xs text-text-muted">Floor {{ $c->floor_price }} ETH · {{ $c->items_count }} items</p>
                    </a>
                @empty
                    <p class="text-sm text-text-muted">No NFT collections listed yet.</p>
                @endforelse
            </div>
            <a href="{{ url('/app/nft') }}" class="mt-3 inline-block text-xs text-brand hover:underline">View All →</a>
        </div>
    </div>

    {{-- Quick nav + searchable asset list --}}
    <div class="glass-card p-5">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-1 text-sm">
                <button type="button" @click="assetFilter = 'all'" :class="assetFilter === 'all' ? 'nav-link-active' : 'nav-link'">Spot</button>
                <button type="button" @click="assetFilter = 'hot'" :class="assetFilter === 'hot' ? 'nav-link-active' : 'nav-link'">Hot</button>
                <button type="button" @click="assetFilter = 'gainers'" :class="assetFilter === 'gainers' ? 'nav-link-active' : 'nav-link'">Gainers</button>
                <button type="button" @click="assetFilter = 'losers'" :class="assetFilter === 'losers' ? 'nav-link-active' : 'nav-link'">Losers</button>
                <a href="{{ url('/app/futures') }}" class="nav-link">Futures</a>
                <a href="{{ url('/app/stocks') }}" class="nav-link">Stocks</a>
            </div>
            <div class="flex items-center rounded-lg border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-muted">
                <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                <input type="text" x-model="search" placeholder="Search pairs..." class="w-40 bg-transparent outline-none placeholder:text-text-muted">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Asset</th><th>Pair</th><th>Price</th><th>24h %</th></tr></thead>
                <tbody>
                    <template x-for="m in filteredAssets" :key="m.symbol">
                        <tr>
                            <td x-text="m.name"></td>
                            <td class="font-numeric" x-text="m.symbol"></td>
                            <td class="font-numeric" x-text="'$' + m.price"></td>
                            <td>
                                <span :class="m.change >= 0 ? 'price-up' : 'price-down'" x-text="(m.change >= 0 ? '+' : '') + m.change + '%'"></span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredAssets.length === 0">
                        <td colspan="4" class="text-center text-text-muted">No matching pairs.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Top Traders --}}
        <div class="glass-card p-5">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-semibold">Top Traders</h2>
                <a href="{{ url('/app/copy-trading') }}" class="text-xs text-brand hover:underline">View all</a>
            </div>
            <div class="space-y-2">
                @forelse ($topTraders as $trader)
                    <a href="{{ route('app.copy-trading.traders.show', $trader) }}" class="flex items-center justify-between rounded-lg border border-border p-3 text-sm hover:border-brand/40">
                        <span class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-gradient text-xs font-bold text-background">{{ substr($trader->display_name, 0, 1) }}</span>
                            <span>
                                <span class="block font-medium">{{ $trader->display_name }}</span>
                                <span class="text-xs text-text-muted">{{ number_format($trader->followers_count) }} followers</span>
                            </span>
                        </span>
                        <x-price-change :value="$trader->return_30d_pct" />
                    </a>
                @empty
                    <p class="text-sm text-text-muted">No traders available yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Watchlist (existing feature, preserved) --}}
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

    {{-- Market News --}}
    <div class="glass-card p-5">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold">Market News</h2>
            <a href="{{ url('/app/news') }}" class="text-xs text-brand hover:underline">View all</a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($news as $article)
                <div class="rounded-xl border border-border p-3">
                    <span class="pill-{{ $article->sentiment === 'bullish' ? 'success' : ($article->sentiment === 'bearish' ? 'danger' : 'muted') }} mb-2">{{ ucfirst($article->sentiment) }}</span>
                    <p class="text-sm font-medium">{{ $article->title }}</p>
                    @if ($article->summary)
                        <p class="mt-1 text-xs text-text-muted">{{ \Illuminate\Support\Str::limit($article->summary, 80) }}</p>
                    @endif
                    <p class="mt-1 text-xs text-text-muted">{{ $article->published_at?->diffForHumans() }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

@php
    $assetListJson = $markets->map(function ($m) {
        return [
            'name' => $m->baseAsset->symbol,
            'symbol' => $m->symbol,
            'price' => number_format($m->quote->price ?? 0, 2, '.', ''),
            'change' => (float) ($m->quote->change_24h_pct ?? 0),
        ];
    })->values();
@endphp
<script>
    function dashboardPage() {
        return {
            search: '',
            assetFilter: 'all',
            featuresOpen: false,
            allAssets: {!! $assetListJson->toJson() !!},
            get filteredAssets() {
                let list = this.allAssets;
                if (this.assetFilter === 'gainers') list = list.filter(m => m.change > 0).sort((a, b) => b.change - a.change);
                else if (this.assetFilter === 'losers') list = list.filter(m => m.change < 0).sort((a, b) => a.change - b.change);
                else if (this.assetFilter === 'hot') list = [...list].sort((a, b) => Math.abs(b.change) - Math.abs(a.change));

                if (this.search.trim() !== '') {
                    const q = this.search.trim().toLowerCase();
                    list = list.filter(m => m.name.toLowerCase().includes(q) || m.symbol.toLowerCase().includes(q));
                }

                return list;
            },
        };
    }
</script>
@endsection
