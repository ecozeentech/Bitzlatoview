@extends('layouts.public')

@section('title', 'Bitzlatoview')

@section('content')
{{-- Live ticker --}}
<div class="border-b border-border bg-surface/60 overflow-hidden">
    <div class="flex animate-ticker whitespace-nowrap py-2 text-xs font-mono">
        @foreach($pairs->concat($pairs) as $pair)
            <span class="mx-6 inline-flex items-center gap-2">
                <span class="text-slate-200">{{ $pair->symbol }}</span>
                <span>{{ number_format($pair->last_price, 2) }}</span>
                <span class="{{ $pair->change_24h >= 0 ? 'price-up' : 'price-down' }}">{{ number_format($pair->change_24h, 2) }}%</span>
            </span>
        @endforeach
    </div>
</div>

{{-- Hero --}}
<section class="page-shell grid items-center gap-10 py-16 lg:grid-cols-2 lg:py-24">
    <div class="animate-fade-up">
        <p class="mb-4 text-xs font-semibold uppercase tracking-[0.25em] text-brand">Bitzlatoview</p>
        <h1 class="font-display text-4xl font-extrabold leading-tight tracking-tight text-slate-50 md:text-5xl lg:text-6xl">
            Trade Crypto, Stocks, Forex, NFTs and Futures on Bitzlatoview
        </h1>
        <p class="section-sub">One dashboard for digital and global markets — built as a simulation-first exchange experience with wallets, P2P, bots, and compliance-ready controls.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('register') }}" class="btn-brand">Get Started</a>
            <a href="{{ route('markets') }}" class="btn-outline">Explore Markets</a>
        </div>
        <p class="risk-banner mt-6 max-w-xl">Trading involves substantial risk of loss. This MVP runs in paper-trading / simulation mode until licensed providers are connected.</p>
    </div>
    <div class="glass-card animate-fade-up p-5 shadow-glow" style="animation-delay:.15s">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-semibold">Market Pulse</h2>
            <span class="badge-warning">Live sim</span>
        </div>
        <table class="data-table">
            <thead><tr><th>Pair</th><th>Price</th><th>24h</th></tr></thead>
            <tbody>
                @foreach($pairs->take(6) as $pair)
                <tr>
                    <td class="font-medium">{{ $pair->symbol }}</td>
                    <td class="font-mono">{{ number_format($pair->last_price, 2) }}</td>
                    <td class="{{ $pair->change_24h >= 0 ? 'price-up' : 'price-down' }}">{{ number_format($pair->change_24h, 2) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

{{-- Gainers / Losers --}}
<section class="page-shell grid gap-6 py-10 lg:grid-cols-2">
    <div class="glass-card p-5">
        <h2 class="section-title text-xl">Top Gainers</h2>
        <table class="data-table mt-4">
            <tbody>
            @foreach($gainers as $g)
                <tr>
                    <td>{{ $g->symbol }}</td>
                    <td class="font-mono">{{ number_format($g->last_price, 2) }}</td>
                    <td class="price-up">+{{ number_format($g->change_24h, 2) }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="glass-card p-5">
        <h2 class="section-title text-xl">Top Losers</h2>
        <table class="data-table mt-4">
            <tbody>
            @foreach($losers as $l)
                <tr>
                    <td>{{ $l->symbol }}</td>
                    <td class="font-mono">{{ number_format($l->last_price, 2) }}</td>
                    <td class="price-down">{{ number_format($l->change_24h, 2) }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>

{{-- Quick panel --}}
<section class="page-shell py-10">
    <div class="glass-card grid gap-4 p-6 md:grid-cols-3">
        <div>
            <h3 class="font-semibold text-brand">Buy Crypto</h3>
            <p class="mt-2 text-sm text-muted">Beginner-friendly buy flow with simulated quotes.</p>
            <a href="{{ route('buy-crypto') }}" class="btn-brand mt-4">Buy</a>
        </div>
        <div>
            <h3 class="font-semibold text-brand">Sell</h3>
            <p class="mt-2 text-sm text-muted">Convert crypto to fiat balance in paper mode.</p>
            <a href="{{ route('app.buy-sell') }}" class="btn-outline mt-4">Sell</a>
        </div>
        <div>
            <h3 class="font-semibold text-brand">Swap</h3>
            <p class="mt-2 text-sm text-muted">Instant convert with fee preview and slippage.</p>
            <a href="{{ route('swap') }}" class="btn-outline mt-4">Swap</a>
        </div>
    </div>
</section>

{{-- Product grid --}}
<section class="page-shell py-14">
    <h2 class="section-title">Everything in one exchange workspace</h2>
    <p class="section-sub">Dense product surface inspired by leading exchange UX — original Bitzlatoview design.</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach([
            ['Spot Trading','Order book, charts, limit & market orders','app.spot'],
            ['Crypto Swap','Convert assets with simulated routes','app.swap'],
            ['P2P Trading','Escrow-protected local payments','p2p'],
            ['Futures','Leverage paper positions with risk gates','futures'],
            ['Copy Trading','Follow verified trader profiles','copy-trading'],
            ['AI Trading Bots','Strategy marketplace, no guaranteed returns','ai-trading-bot'],
            ['Mining','Hashrate contracts with transparent estimates','mining'],
            ['NFT Marketplace','Collections, floor prices, WalletConnect','nft'],
            ['Stocks','Paper equity trading module','stocks'],
            ['Forex','Major pairs with spread simulation','forex'],
            ['MetaTrader 5','Broker/MT5 connection placeholder','metatrader-5'],
            ['Virtual Cards','KYC-gated mock issuing flow','app.virtual-cards'],
        ] as [$title, $desc, $route])
        <a href="{{ Route::has($route) ? route($route) : url('/'.$route) }}" class="glass-card block p-5 transition hover:border-brand/40 hover:shadow-glow">
            <h3 class="font-semibold">{{ $title }}</h3>
            <p class="mt-2 text-sm text-muted">{{ $desc }}</p>
        </a>
        @endforeach
    </div>
</section>

{{-- Wallets --}}
<section class="border-y border-border bg-surface/40 py-16">
    <div class="page-shell">
        <h2 class="section-title">Three wallets. One ledger.</h2>
        <p class="section-sub">Primary, Trading, and Investment wallets with double-entry accounting — no direct balance edits.</p>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach([['Primary Wallet','Deposits, withdrawals, P2P, cards'],['Trading Wallet','Spot, futures collateral, fees'],['Investment Wallet','Bots, copy trading, mining, earn']] as [$t,$d])
            <div class="glass-card p-6">
                <h3 class="text-lg font-semibold text-brand">{{ $t }}</h3>
                <p class="mt-2 text-sm text-muted">{{ $d }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- P2P --}}
<section class="page-shell py-16">
    <div class="grid items-center gap-8 lg:grid-cols-2">
        <div>
            <h2 class="section-title">P2P with escrow protection</h2>
            <p class="section-sub">Buy and sell crypto with local payment methods, merchant badges, chat, and an appeal system.</p>
            <a href="{{ route('p2p') }}" class="btn-brand mt-6">Open P2P Marketplace</a>
        </div>
        <div class="glass-card p-5">
            <p class="text-xs uppercase tracking-wide text-muted mb-3">Active ads</p>
            @forelse(\App\Models\P2PAd::query()->where('status','active')->with('asset')->limit(4)->get() as $ad)
                <div class="flex items-center justify-between border-b border-border/60 py-3 text-sm">
                    <span>{{ $ad->side }} {{ $ad->asset->symbol ?? '—' }} / {{ $ad->fiat_currency }}</span>
                    <span class="font-mono text-brand">{{ number_format($ad->price, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-muted">Seed demo ads after setup.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Copy / AI / Mining --}}
<section class="page-shell grid gap-6 py-10 lg:grid-cols-3">
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold">Copy Trading</h3>
        <p class="mt-2 text-sm text-muted">Follow verified traders with risk scores and return history. Past performance is not indicative of future results.</p>
        <div class="mt-4 space-y-3">
            @foreach($traders as $t)
                <div class="flex justify-between text-sm"><span>{{ $t->display_name }}</span><span class="{{ $t->return_30d >= 0 ? 'price-up' : 'price-down' }}">{{ number_format($t->return_30d,1) }}%</span></div>
            @endforeach
        </div>
    </div>
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold">AI Trading Bots</h3>
        <p class="mt-2 text-sm text-muted">Strategy marketplace with allocation from Investment Wallet. Experimental — may lose money.</p>
        <div class="mt-4 space-y-3">
            @foreach($bots as $b)
                <div class="flex justify-between text-sm"><span>{{ $b->name }}</span><span class="text-purple">{{ ucfirst($b->strategy_type) }}</span></div>
            @endforeach
        </div>
    </div>
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold">Crypto Mining</h3>
        <p class="mt-2 text-sm text-muted">Contract cards with hashrate, term, and estimated rewards. Simulated payouts in MVP.</p>
        <div class="mt-4 space-y-3">
            @foreach($mining as $m)
                <div class="flex justify-between text-sm"><span>{{ $m->name }}</span><span class="font-mono">{{ $m->hashrate }} {{ $m->hashrate_unit }}</span></div>
            @endforeach
        </div>
    </div>
</section>

{{-- MT5 / NFT / Cards / Trust --}}
<section class="page-shell grid gap-6 py-10 md:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('metatrader-5') }}" class="glass-card p-5"><h3 class="font-semibold">MetaTrader 5</h3><p class="mt-2 text-sm text-muted">Connect broker accounts, sync positions, launch web terminal placeholder.</p></a>
    <a href="{{ route('nft') }}" class="glass-card p-5"><h3 class="font-semibold">NFT Collections</h3><p class="mt-2 text-sm text-muted">{{ $nfts->count() }} featured collections with floor & volume.</p></a>
    <a href="{{ route('app.virtual-cards') }}" class="glass-card p-5"><h3 class="font-semibold">Virtual Cards</h3><p class="mt-2 text-sm text-muted">KYC-gated mock cards. Real issuing via Stripe Issuing / partners later.</p></a>
    <a href="{{ route('security') }}" class="glass-card p-5"><h3 class="font-semibold">Security & Compliance</h3><p class="mt-2 text-sm text-muted">KYC/AML gates, audit logs, maker/checker adjustments, risk disclosures.</p></a>
</section>

{{-- News / Blog --}}
<section class="page-shell py-14">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="section-title">News & insights</h2>
            <p class="section-sub">Market news and Bitzlatoview research notes.</p>
        </div>
        <a href="{{ route('blog') }}" class="btn-ghost">View blog</a>
    </div>
    <div class="mt-8 grid gap-4 md:grid-cols-3">
        @foreach($news as $article)
            <article class="glass-card p-5">
                <span class="badge-{{ $article->sentiment === 'bullish' ? 'success' : ($article->sentiment === 'bearish' ? 'danger' : 'muted') }}">{{ ucfirst($article->sentiment) }}</span>
                <h3 class="mt-3 font-semibold">{{ $article->title }}</h3>
                <p class="mt-2 text-sm text-muted line-clamp-3">{{ $article->summary }}</p>
            </article>
        @endforeach
    </div>
</section>

{{-- FAQ --}}
<section class="page-shell py-14">
    <h2 class="section-title">FAQ</h2>
    <div class="mt-6 space-y-3" x-data="{ open: null }">
        @foreach($faqs as $i => $faq)
        <div class="glass-card overflow-hidden">
            <button class="flex w-full items-center justify-between px-5 py-4 text-left font-medium" @click="open = open === {{ $i }} ? null : {{ $i }}">
                {{ $faq->question }}
                <span class="text-brand">+</span>
            </button>
            <div class="px-5 pb-4 text-sm text-muted" x-show="open === {{ $i }}" x-cloak>{{ $faq->answer }}</div>
        </div>
        @endforeach
    </div>
</section>

{{-- Final CTA --}}
<section class="page-shell pb-20">
    <div class="glass-card bg-surface-glow px-8 py-12 text-center shadow-glow">
        <h2 class="font-display text-3xl font-extrabold">Start on Bitzlatoview</h2>
        <p class="mx-auto mt-3 max-w-xl text-muted">Create an account, explore simulated markets, and learn the full wallet → trade → P2P workflow before going live.</p>
        <div class="mt-8 flex justify-center gap-3">
            <a href="{{ route('register') }}" class="btn-brand">Create account</a>
            <a href="{{ route('risk-disclosure') }}" class="btn-outline">Read risk disclosure</a>
        </div>
    </div>
</section>
@endsection
