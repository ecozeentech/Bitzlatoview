@php
    $nav = [
        null => [['Dashboard', 'app.dashboard', 'app/dashboard']],
        'Trade' => [
            ['Markets', 'app.markets', 'app/markets'],
            ['Spot Trading', 'app.spot', 'app/spot'],
            ['Buy / Sell', 'app.buy-sell', 'app/buy-sell'],
            ['Swap', 'app.swap', 'app/swap'],
            ['Futures', 'app.futures', 'app/futures'],
            ['Stocks', 'app.stocks', 'app/stocks'],
            ['Forex', 'app.forex', 'app/forex'],
            ['MetaTrader 5', 'app.mt5', 'app/metatrader-5'],
        ],
        'Earn & Automate' => [
            ['P2P', 'app.p2p', 'app/p2p'],
            ['Copy Trading', 'app.copy-trading', 'app/copy-trading'],
            ['AI Bots', 'app.ai-bots', 'app/ai-bots'],
            ['Mining', 'app.mining', 'app/mining'],
            ['Investments', 'app.investments', 'app/investments'],
            ['NFT', 'app.nft', 'app/nft'],
        ],
        'Wallets' => [
            ['Primary Wallet', 'app.wallet.primary', 'app/wallet/primary'],
            ['Trading Wallet', 'app.wallet.trading', 'app/wallet/trading'],
            ['Investment Wallet', 'app.wallet.investment', 'app/wallet/investment'],
            ['Deposit', 'app.funding.deposit', 'app/funding/deposit'],
            ['Withdraw', 'app.funding.withdraw', 'app/funding/withdraw'],
            ['Transaction History', 'app.funding.transactions', 'app/funding/transactions'],
        ],
        'Account' => [
            ['Virtual Cards', 'app.virtual-cards', 'app/virtual-cards'],
            ['Tax Center', 'app.tax', 'app/tax'],
            ['Analyst Packages', 'app.analyst-packages', 'app/analyst-packages'],
            ['News', 'app.news', 'app/news'],
            ['Blog', 'app.blog', 'app/blog'],
            ['Referrals', 'app.referrals', 'app/referrals'],
            ['Support', 'app.support', 'app/support'],
            ['Settings', 'app.settings.profile', 'app/settings'],
        ],
    ];
@endphp
{{-- Mobile backdrop --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-30 bg-black/60 lg:hidden"></div>

<aside
    class="fixed inset-y-0 left-0 z-40 w-72 max-w-[85vw] -translate-x-full transform overflow-y-auto border-r border-border bg-surface transition-transform duration-200 ease-in-out lg:static lg:z-auto lg:w-64 lg:max-w-none lg:translate-x-0 lg:shrink-0 lg:bg-surface/60"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex h-16 items-center justify-between border-b border-border px-4 lg:hidden">
        <x-site-logo textClass="text-base font-extrabold" />
        <button type="button" @click="sidebarOpen = false" class="rounded-lg p-2 text-text-muted hover:bg-surface-2 hover:text-text-main" aria-label="Close menu">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="flex h-[calc(100vh-4rem)] flex-col overflow-y-auto px-3 py-4 lg:sticky lg:top-16">
        @foreach ($nav as $section => $items)
            <div class="mb-4">
                @if ($section)
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-text-muted/70">{{ $section }}</p>
                @endif
                @foreach ($items as [$label, $routeName, $prefix])
                    <a href="{{ url($prefix) }}" @click="sidebarOpen = false" class="{{ request()->is($prefix.'*') ? 'nav-link-active' : 'nav-link' }} block">{{ $label }}</a>
                @endforeach
            </div>
        @endforeach
    </div>
</aside>
