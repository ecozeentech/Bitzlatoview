<aside class="hidden w-64 shrink-0 border-r border-border bg-surface lg:block">
    <div class="sticky top-0 flex h-screen flex-col overflow-y-auto p-4">
        <a href="{{ route('home') }}" class="mb-6 flex items-center gap-2 px-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand text-xs font-extrabold text-background">BZ</span>
            <span class="font-bold">Bitzlatoview</span>
        </a>
        <nav class="space-y-1 text-sm">
            @php
            $links = [
                ['Dashboard','app.dashboard'],
                ['Markets','app.markets'],
                ['Buy/Sell','app.buy-sell'],
                ['Spot','app.spot'],
                ['Swap','app.swap'],
                ['Futures','app.futures'],
                ['P2P','app.p2p'],
                ['Copy Trading','app.copy-trading'],
                ['AI Bots','app.ai-bots'],
                ['Mining','app.mining'],
                ['Investments','app.investments'],
                ['Stocks','app.stocks'],
                ['Forex','app.forex'],
                ['MetaTrader 5','app.metatrader'],
                ['NFT','app.nft'],
                ['Wallets','app.wallet'],
                ['Virtual Cards','app.virtual-cards'],
                ['Tax Center','app.tax'],
                ['News','app.news'],
                ['Referrals','app.referrals'],
                ['Support','app.support'],
                ['Settings','app.settings'],
            ];
            @endphp
            @foreach($links as [$label, $route])
                <a href="{{ route($route) }}" class="sidebar-link {{ request()->routeIs($route) || request()->routeIs($route.'.*') ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
            @if(auth()->user()?->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link mt-4 border border-brand/30 text-brand">Admin</a>
            @endif
        </nav>
    </div>
</aside>
