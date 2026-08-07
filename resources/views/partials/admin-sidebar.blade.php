@php
    $adminNav = [
        null => [['Dashboard', 'admin/dashboard']],
        'Users & Risk' => [
            ['Users', 'admin/users'],
            ['KYC Queue', 'admin/kyc'],
            ['Risk & Compliance', 'admin/risk'],
            ['Audit Logs', 'admin/audit-logs'],
        ],
        'Funds' => [
            ['Payment Settings', 'admin/payment-methods'],
            ['Deposit Requests', 'admin/deposits'],
            ['Withdrawal Requests', 'admin/withdrawals'],
            ['Ledger', 'admin/ledger'],
            ['Balance Adjustments', 'admin/adjustments'],
        ],
        'Trading' => [
            ['Markets & Assets', 'admin/markets'],
            ['Orders & Trades', 'admin/orders'],
            ['Swap', 'admin/swap'],
            ['P2P', 'admin/p2p'],
            ['Copy Trading', 'admin/copy-trading'],
            ['AI Bots', 'admin/ai-bots'],
            ['Mining', 'admin/mining'],
            ['Investments', 'admin/investments'],
            ['Stocks / Forex / Futures', 'admin/markets-extended'],
            ['MetaTrader', 'admin/metatrader'],
        ],
        'Products' => [
            ['NFT', 'admin/nft'],
            ['Virtual Cards', 'admin/virtual-cards'],
            ['Tax Center', 'admin/tax'],
            ['Billing Packages', 'admin/billing'],
        ],
        'Content' => [
            ['Blog', 'admin/blog'],
            ['News', 'admin/news'],
            ['FAQ', 'admin/faq'],
            ['CMS Pages', 'admin/cms'],
        ],
        'Communications' => [
            ['Email Templates', 'admin/email/templates'],
            ['Email Campaigns', 'admin/email/campaigns'],
            ['Email Logs', 'admin/email/logs'],
            ['Support Tickets', 'admin/support'],
        ],
        'System' => [
            ['Feature Flags', 'admin/settings/feature-flags'],
            ['Platform Settings', 'admin/settings'],
            ['Branding', 'admin/settings/branding'],
        ],
    ];
@endphp
<aside class="hidden w-64 shrink-0 border-r border-border bg-surface/60 lg:block">
    <div class="sticky top-16 flex h-[calc(100vh-4rem)] flex-col overflow-y-auto px-3 py-4">
        @foreach ($adminNav as $section => $items)
            <div class="mb-4">
                @if ($section)
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-text-muted/70">{{ $section }}</p>
                @endif
                @foreach ($items as [$label, $prefix])
                    <a href="{{ url($prefix) }}" class="{{ request()->is($prefix.'*') ? 'nav-link-active' : 'nav-link' }} block">{{ $label }}</a>
                @endforeach
            </div>
        @endforeach
    </div>
</aside>
