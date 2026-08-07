@extends('layouts.public')

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden border-b border-border">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_-10%,rgba(245,179,1,0.15),transparent_60%),radial-gradient(circle_at_90%_10%,rgba(124,58,237,0.15),transparent_50%)]"></div>
    <div class="mx-auto max-w-7xl px-4 py-20 lg:px-8 lg:py-28">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <span class="pill-warning mb-4">Compliance-first exchange</span>
                <h1 class="text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                    Trade Crypto, Stocks, Forex, NFTs and Futures on <span class="text-brand">Bitzlatoview</span>
                </h1>
                <p class="mt-5 max-w-lg text-lg text-text-muted">
                    One modern dashboard for digital and global markets — spot, swap, P2P, futures, copy trading, AI bots, mining and more. Built compliance-first, with every fund movement reviewed by our team and recorded on a real double-entry ledger.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('register') }}" class="btn-brand">Get Started</a>
                    <a href="/markets" class="btn-outline">Explore Markets</a>
                </div>
                <p class="mt-6 text-xs text-text-muted">No guaranteed returns. Trading involves risk of loss. <a href="/risk-disclosure" class="underline hover:text-brand">Read our risk disclosure</a>.</p>
            </div>

            <div class="glass-card p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold">Live Market Snapshot</h3>
                    <span class="pill-success">Live feed</span>
                </div>
                <div class="space-y-3">
                    @foreach ($markets->take(5) as $market)
                        <div class="flex items-center justify-between rounded-lg bg-surface-2/60 px-3 py-2.5">
                            <div class="flex items-center gap-3">
                                <x-asset-icon :symbol="$market->baseAsset->symbol" />
                                <div>
                                    <p class="text-sm font-semibold">{{ $market->symbol }}</p>
                                    <p class="text-xs text-text-muted">Vol {{ number_format($market->quote->volume_24h / 1000000, 1) }}M</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-numeric text-sm">${{ number_format($market->quote->price, $market->quote->price < 1 ? 4 : 2) }}</p>
                                <x-price-change :value="$market->quote->change_24h_pct" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- TICKER --}}
<div class="overflow-hidden border-b border-border bg-surface/40 py-2.5">
    <div class="flex animate-[marquee_30s_linear_infinite] gap-10 whitespace-nowrap px-4 text-sm">
        @foreach ($markets->concat($markets) as $market)
            <span class="flex items-center gap-2 font-numeric">
                <span class="font-semibold text-text-main">{{ $market->symbol }}</span>
                <span>${{ number_format($market->quote->price, 2) }}</span>
                <x-price-change :value="$market->quote->change_24h_pct" />
            </span>
        @endforeach
    </div>
</div>
<style>@keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }</style>

{{-- TOP GAINERS / LOSERS --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="glass-card p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-semibold">Top Gainers (24h)</h3>
                <a href="/markets/top-gainers" class="text-sm text-brand hover:underline">View all</a>
            </div>
            @foreach ($topGainers as $m)
                <div class="flex items-center justify-between border-b border-border/60 py-2.5 last:border-0">
                    <span class="flex items-center gap-2"><x-asset-icon :symbol="$m->baseAsset->symbol" /> {{ $m->symbol }}</span>
                    <div class="text-right"><p class="font-numeric text-sm">${{ number_format($m->quote->price, 2) }}</p><x-price-change :value="$m->quote->change_24h_pct" /></div>
                </div>
            @endforeach
        </div>
        <div class="glass-card p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-semibold">Top Losers (24h)</h3>
                <a href="/markets/top-losers" class="text-sm text-brand hover:underline">View all</a>
            </div>
            @foreach ($topLosers as $m)
                <div class="flex items-center justify-between border-b border-border/60 py-2.5 last:border-0">
                    <span class="flex items-center gap-2"><x-asset-icon :symbol="$m->baseAsset->symbol" /> {{ $m->symbol }}</span>
                    <div class="text-right"><p class="font-numeric text-sm">${{ number_format($m->quote->price, 2) }}</p><x-price-change :value="$m->quote->change_24h_pct" /></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- QUICK BUY/SELL/SWAP --}}
<section class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
    <div class="glass-card grid gap-6 p-6 md:grid-cols-3">
        <div>
            <h4 class="mb-2 font-semibold text-success">Buy</h4>
            <p class="text-sm text-text-muted">Buy crypto instantly using your Bitzlatoview balance.</p>
            <a href="/buy-crypto" class="btn-outline mt-4 w-full text-sm">Buy Crypto</a>
        </div>
        <div>
            <h4 class="mb-2 font-semibold text-danger">Sell</h4>
            <p class="text-sm text-text-muted">Sell crypto back to your fiat balance in a couple of clicks.</p>
            <a href="/buy-crypto" class="btn-outline mt-4 w-full text-sm">Sell Crypto</a>
        </div>
        <div>
            <h4 class="mb-2 font-semibold text-info">Swap</h4>
            <p class="text-sm text-text-muted">Convert between assets instantly with transparent live rates.</p>
            <a href="/swap" class="btn-outline mt-4 w-full text-sm">Swap Now</a>
        </div>
    </div>
</section>

{{-- PRODUCT GRID --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <h2 class="mb-8 text-center text-2xl font-bold">Everything you need in one exchange</h2>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $products = [
                ['Spot Trading', 'Buy and sell crypto with a full order book.', '/crypto', '📈'],
                ['Crypto Swap', 'Instant asset conversion, no order book needed.', '/swap', '🔁'],
                ['P2P Trading', 'Escrow-protected peer-to-peer trading.', '/p2p', '🤝'],
                ['Futures', 'Leveraged perpetual futures — high risk.', '/futures', '⚡'],
                ['Copy Trading', 'Follow verified traders automatically.', '/copy-trading', '👥'],
                ['AI Trading Bots', 'Automated strategies. No guaranteed returns.', '/ai-trading-bot', '🤖'],
                ['Mining', 'Transparent hashpower contracts.', '/mining', '⛏️'],
                ['NFT Marketplace', 'Browse and collect digital assets.', '/nft', '🖼️'],
                ['Stocks', 'Paper-trade major listed equities.', '/stocks', '📊'],
                ['Forex', 'Major/minor currency pairs.', '/forex', '💱'],
                ['MetaTrader 5', 'Connect broker accounts, sync positions.', '/metatrader-5', '🖥️'],
                ['Virtual Cards', 'Card account records, pending a licensed issuer.', '/app/virtual-cards', '💳'],
            ];
        @endphp
        @foreach ($products as [$title, $desc, $href, $icon])
            <a href="{{ $href }}" class="glass-card group p-5 transition hover:border-brand/50">
                <span class="text-2xl">{{ $icon }}</span>
                <h3 class="mt-3 font-semibold group-hover:text-brand">{{ $title }}</h3>
                <p class="mt-1 text-sm text-text-muted">{{ $desc }}</p>
            </a>
        @endforeach
    </div>
</section>

{{-- WALLETS --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="glass-card grid gap-8 p-8 md:grid-cols-3">
        <div>
            <h3 class="font-semibold text-brand">Primary Wallet</h3>
            <p class="mt-2 text-sm text-text-muted">Your funding hub for deposits, withdrawals, P2P and card top-ups.</p>
        </div>
        <div>
            <h3 class="font-semibold text-info">Trading Wallet</h3>
            <p class="mt-2 text-sm text-text-muted">Collateral for spot orders and futures positions, kept separate from savings.</p>
        </div>
        <div>
            <h3 class="font-semibold text-purple">Investment Wallet</h3>
            <p class="mt-2 text-sm text-text-muted">Allocate to AI bots, copy trading, mining contracts and Earn products.</p>
        </div>
    </div>
</section>

{{-- P2P PROMO --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="grid items-center gap-10 lg:grid-cols-2">
        <div>
            <span class="pill-info mb-3">P2P Marketplace</span>
            <h2 class="text-2xl font-bold">Buy and sell crypto with local payment methods</h2>
            <ul class="mt-5 space-y-3 text-sm text-text-muted">
                <li>✓ Escrow-protected trades — crypto is locked until both sides confirm.</li>
                <li>✓ Verified merchant badges with completion rate and release time.</li>
                <li>✓ In-app chat, evidence upload and an admin-reviewed appeal system.</li>
            </ul>
            <a href="/p2p" class="btn-brand mt-6 inline-flex">Explore P2P</a>
        </div>
        <div class="glass-card p-5">
            <p class="mb-3 text-sm font-semibold text-text-muted">Sample sell ad</p>
            <div class="rounded-lg bg-surface-2/60 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="pill-success">Verified Merchant</span>
                    <span class="text-xs text-text-muted">98.7% completion</span>
                </div>
                <p class="mt-3 font-numeric text-lg">1 USDT = $1.001</p>
                <p class="text-xs text-text-muted">Limit: $20 – $5,000 · Bank Transfer</p>
            </div>
        </div>
    </div>
</section>

{{-- COPY TRADING / AI BOTS / MINING PROMO --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="glass-card p-6">
            <span class="pill-warning">Copy Trading</span>
            <h3 class="mt-3 font-semibold">Follow verified traders</h3>
            <p class="mt-2 text-sm text-text-muted">Risk score, drawdown and return history are shown for every trader. Past performance never guarantees future results.</p>
            <a href="/copy-trading" class="mt-4 inline-block text-sm text-brand hover:underline">Browse traders →</a>
        </div>
        <div class="glass-card p-6">
            <span class="pill-info">AI Trading Bots</span>
            <h3 class="mt-3 font-semibold">Strategy marketplace</h3>
            <p class="mt-2 text-sm text-text-muted">Grid, DCA, trend and arbitrage strategies with disclosed backtested performance. Experimental — may lose money.</p>
            <a href="/ai-trading-bot" class="mt-4 inline-block text-sm text-brand hover:underline">View bots →</a>
        </div>
        <div class="glass-card p-6">
            <span class="pill-success">Mining</span>
            <h3 class="mt-3 font-semibold">Hashpower contracts</h3>
            <p class="mt-2 text-sm text-text-muted">Transparent hashrate, term and fees. Rewards follow the disclosed rate and are never guaranteed.</p>
            <a href="/mining" class="mt-4 inline-block text-sm text-brand hover:underline">View contracts →</a>
        </div>
    </div>
</section>

{{-- MT5 PROMO --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="glass-card flex flex-col items-center gap-6 p-8 text-center md:flex-row md:text-left">
        <div class="flex-1">
            <span class="pill-info">MetaTrader 5</span>
            <h3 class="mt-3 text-xl font-bold">Connect your broker or MT5 account</h3>
            <p class="mt-2 text-sm text-text-muted">Sync positions and trade history, view a web terminal placeholder, and explore EA/copy-signal marketplaces — all broker/provider integrations are clearly marked for production licensing.</p>
        </div>
        <a href="/metatrader-5" class="btn-outline shrink-0">Learn More</a>
    </div>
</section>

{{-- NEWS & BLOG --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-2">
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold">Crypto News</h3>
                <a href="/news" class="text-sm text-brand hover:underline">View all</a>
            </div>
            <div class="space-y-3">
                @foreach ($news as $article)
                    <a href="/news" class="glass-card block p-4">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="pill-{{ $article->sentiment === 'bullish' ? 'success' : ($article->sentiment === 'bearish' ? 'danger' : 'muted') }}">{{ ucfirst($article->sentiment) }}</span>
                            <span class="text-text-muted">{{ $article->published_at?->diffForHumans() }}</span>
                        </div>
                        <p class="mt-2 font-medium">{{ $article->title }}</p>
                    </a>
                @endforeach
            </div>
        </div>
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold">From the Blog</h3>
                <a href="/blog" class="text-sm text-brand hover:underline">View all</a>
            </div>
            <div class="space-y-3">
                @foreach ($blogPosts as $post)
                    <a href="{{ route('blog.show', $post->slug) }}" class="glass-card block p-4">
                        <p class="text-xs text-text-muted">{{ $post->category }} · {{ $post->published_at?->format('M j, Y') }}</p>
                        <p class="mt-2 font-medium">{{ $post->title }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- NFT --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <h3 class="text-lg font-bold">Trending NFT Collections</h3>
        <a href="/nft" class="text-sm text-brand hover:underline">View marketplace</a>
    </div>
    <div class="grid gap-5 sm:grid-cols-3">
        @foreach ($collections as $collection)
            <a href="/nft" class="glass-card block overflow-hidden p-5">
                <div class="mb-3 h-28 rounded-lg bg-brand-gradient/20"></div>
                <p class="font-semibold">{{ $collection->name }}</p>
                <div class="mt-2 flex justify-between text-xs text-text-muted">
                    <span>Floor {{ $collection->floor_price }} ETH</span>
                    <span>{{ number_format($collection->owners_count) }} owners</span>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- VIRTUAL CARD --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="glass-card grid items-center gap-8 p-8 md:grid-cols-2">
        <div>
            <span class="pill-warning">Virtual Cards</span>
            <h3 class="mt-3 text-xl font-bold">Spend your balance anywhere online</h3>
            <p class="mt-2 text-sm text-text-muted">Create a virtual card funded from your Primary Wallet. Real, spendable cards require a licensed bank-partner processor (e.g. Stripe Issuing, Marqeta, Lithic) under Visa/Mastercard rules — cards created today are account records only until that integration goes live.</p>
            <a href="/app/virtual-cards" class="btn-brand mt-5 inline-flex">Get a Virtual Card</a>
        </div>
        <div class="mx-auto h-48 w-80 rounded-2xl bg-gradient-to-br from-surface-2 to-background p-5 shadow-glass ring-1 ring-border">
            <p class="text-xs text-text-muted">Bitzlatoview</p>
            <p class="mt-8 font-numeric text-lg tracking-widest">•••• •••• •••• 4821</p>
            <div class="mt-6 flex justify-between text-xs text-text-muted">
                <span>CARDHOLDER NAME</span>
                <span>12/29</span>
            </div>
        </div>
    </div>
</section>

{{-- SECURITY / TRUST --}}
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <h2 class="mb-8 text-center text-2xl font-bold">Built compliance-first</h2>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Double-Entry Ledger', 'Every balance change is a matched, auditable debit/credit pair.'],
            ['No Direct Balance Edits', 'Admin adjustments require maker/checker approval with evidence.'],
            ['KYC/AML Gates', 'Withdrawals, cards, futures and P2P merchant tools require verification.'],
            ['Full Audit Logging', 'Every sensitive admin and user action is logged with before/after state.'],
        ] as [$title, $desc])
            <div class="glass-card p-5">
                <h4 class="font-semibold text-brand">{{ $title }}</h4>
                <p class="mt-2 text-sm text-text-muted">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- FAQ --}}
<section class="mx-auto max-w-4xl px-4 py-16 lg:px-8" x-data="{ open: null }">
    <h2 class="mb-8 text-center text-2xl font-bold">Frequently Asked Questions</h2>
    <div class="space-y-3">
        @foreach (\App\Models\FaqItem::orderBy('sort_order')->take(6)->get() as $i => $faq)
            <div class="glass-card overflow-hidden">
                <button class="flex w-full items-center justify-between p-4 text-left font-medium" @click="open = open === {{ $i }} ? null : {{ $i }}">
                    {{ $faq->question }}
                    <svg class="h-4 w-4 shrink-0 transition" :class="{ 'rotate-180': open === {{ $i }} }" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                </button>
                <div x-show="open === {{ $i }}" x-transition class="px-4 pb-4 text-sm text-text-muted">{{ $faq->answer }}</div>
            </div>
        @endforeach
    </div>
</section>

{{-- FINAL CTA --}}
<section class="mx-auto max-w-7xl px-4 py-20 lg:px-8">
    <div class="glass-card flex flex-col items-center gap-6 p-10 text-center">
        <h2 class="text-2xl font-bold">Ready to explore Bitzlatoview?</h2>
        <p class="max-w-xl text-text-muted">Create a free account and start exploring every market with full feature access.</p>
        <a href="{{ route('register') }}" class="btn-brand">Create Free Account</a>
    </div>
</section>

@endsection
