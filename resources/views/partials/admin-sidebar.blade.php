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
            ['Signals', 'admin/signals'],
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
            ['Messages to Users', 'admin/messages'],
            ['Support Tickets', 'admin/support'],
        ],
        'System' => [
            ['Feature Flags', 'admin/settings/feature-flags'],
            ['Platform Settings', 'admin/settings'],
            ['Branding', 'admin/settings/branding'],
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
        <span class="flex items-center gap-2 text-base font-extrabold"><x-site-logo textClass="text-base font-extrabold" /> <span class="pill-info">Admin</span></span>
        <button type="button" @click="sidebarOpen = false" class="rounded-lg p-2 text-text-muted hover:bg-surface-2 hover:text-text-main" aria-label="Close menu">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="flex h-[calc(100vh-4rem)] flex-col overflow-y-auto px-3 py-4 lg:sticky lg:top-16">
        @foreach ($adminNav as $section => $items)
            <div class="mb-4">
                @if ($section)
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-text-muted/70">{{ $section }}</p>
                @endif
                @foreach ($items as [$label, $prefix])
                    <a href="{{ url($prefix) }}" @click="sidebarOpen = false" class="{{ request()->is($prefix.'*') ? 'nav-link-active' : 'nav-link' }} block">{{ $label }}</a>
                @endforeach
            </div>
        @endforeach
    </div>
</aside>
