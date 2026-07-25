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
<aside class="hidden w-64 shrink-0 border-r border-border bg-surface/60 lg:block">
    <div class="sticky top-16 flex h-[calc(100vh-4rem)] flex-col overflow-y-auto px-3 py-4">
        @foreach ($nav as $section => $items)
            <div class="mb-4">
                @if ($section)
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-text-muted/70">{{ $section }}</p>
                @endif
                @foreach ($items as [$label, $routeName, $prefix])
                    <a href="{{ url($prefix) }}" class="{{ request()->is($prefix.'*') ? 'nav-link-active' : 'nav-link' }} block">{{ $label }}</a>
                @endforeach
            </div>
        @endforeach
    </div>
</aside>
