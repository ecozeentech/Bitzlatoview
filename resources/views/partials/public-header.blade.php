<header class="sticky top-0 z-50 border-b border-border/80 bg-background/85 backdrop-blur-xl">
    <div class="page-shell flex h-16 items-center justify-between gap-4">
        <div class="flex items-center gap-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand font-display text-sm font-extrabold text-background">BZ</span>
                <span class="font-display text-lg font-extrabold tracking-tight text-slate-50">Bitzlatoview</span>
            </a>
            <nav class="hidden items-center gap-1 lg:flex">
                @foreach([
                    ['Buy Crypto', [['Buy with Card','buy-crypto'],['Bank Deposit','buy-crypto'],['P2P Trading','p2p'],['Convert/Swap','swap']]],
                    ['Markets', [['Crypto Markets','markets'],['Top Gainers','markets.top-gainers'],['New Listings','markets.new-listings'],['Stocks','stocks'],['Forex','forex'],['Futures','futures'],['NFTs','nft']]],
                    ['Trade', [['Spot','app.spot'],['Futures','futures'],['Swap','swap'],['Trading Bots','ai-trading-bot'],['Copy Trading','copy-trading']]],
                    ['Finance', [['Mining','mining'],['Investments','investments'],['Virtual Card','app.virtual-cards']]],
                    ['Learn', [['News','news'],['Blog','blog'],['FAQ','faq']]],
                ] as [$label, $items])
                <div class="group relative">
                    <button class="btn-ghost">{{ $label }}</button>
                    <div class="mega-panel !w-72 !p-3">
                        <div class="grid gap-1">
                            @foreach($items as [$name, $route])
                                <a href="{{ Route::has($route) ? route($route) : url('/'.$route) }}" class="rounded-lg px-3 py-2 text-sm text-muted hover:bg-surface-2 hover:text-slate-100">{{ $name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </nav>
        </div>
        <div class="flex items-center gap-2">
            @auth
                <a href="{{ route('app.dashboard') }}" class="btn-outline">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
                <a href="{{ route('register') }}" class="btn-brand">Get Started</a>
            @endauth
        </div>
    </div>
</header>
