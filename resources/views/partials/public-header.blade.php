<header class="sticky top-0 z-50 border-b border-border bg-background/90 backdrop-blur-xl" x-data="{ mobileOpen: false }">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 lg:px-8">
        <div class="flex items-center gap-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <x-site-logo />
            </a>

            <nav class="hidden items-center gap-1 lg:flex" x-data="{ open: null }" @mouseleave="open = null">
                @php
                    $megaMenu = [
                        'Buy Crypto' => [
                            ['Buy with Card', '/buy-crypto'],
                            ['Bank Deposit', '/buy-crypto'],
                            ['P2P Trading', '/p2p'],
                            ['Convert / Swap', '/swap'],
                        ],
                        'Markets' => [
                            ['Crypto Markets', '/markets'],
                            ['Top Gainers', '/markets/top-gainers'],
                            ['New Listings', '/markets/new-listings'],
                            ['Stocks', '/stocks'],
                            ['Forex', '/forex'],
                            ['Futures', '/futures'],
                            ['NFTs', '/nft'],
                        ],
                        'Trade' => [
                            ['Spot', '/crypto'],
                            ['Futures', '/futures'],
                            ['Swap', '/swap'],
                            ['Trading Bots', '/ai-trading-bot'],
                            ['Copy Trading', '/copy-trading'],
                        ],
                        'Finance' => [
                            ['Primary Wallet', '/app/wallet/primary'],
                            ['Trading Wallet', '/app/wallet/trading'],
                            ['Investment Wallet', '/app/wallet/investment'],
                            ['Mining', '/mining'],
                            ['Investments', '/investments'],
                            ['Virtual Card', '/app/virtual-cards'],
                        ],
                        'Web3' => [
                            ['WalletConnect', '/app/settings/wallet-connect'],
                            ['NFT Marketplace', '/nft'],
                        ],
                        'Learn' => [
                            ['News', '/news'],
                            ['Blog', '/blog'],
                            ['FAQ', '/faq'],
                        ],
                        'Company' => [
                            ['About', '/about'],
                            ['Contact', '/contact'],
                            ['Security', '/security'],
                            ['Fees', '/fees'],
                        ],
                    ];
                @endphp

                @foreach ($megaMenu as $label => $items)
                    <div class="relative" @mouseenter="open = '{{ $label }}'">
                        <button class="nav-link flex items-center gap-1">
                            {{ $label }}
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
                        </button>
                        <div x-show="open === '{{ $label }}'" x-transition class="absolute left-0 top-full w-56 rounded-xl border border-border bg-surface p-2 shadow-glass">
                            @foreach ($items as [$itemLabel, $href])
                                <a href="{{ $href }}" class="block rounded-lg px-3 py-2 text-sm text-text-muted hover:bg-surface-2 hover:text-text-main">{{ $itemLabel }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </div>

        <div class="hidden items-center gap-3 lg:flex">
            <x-language-switcher />
            @auth
                <a href="{{ route('dashboard') }}" class="btn-outline text-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="nav-link">Log In</a>
                <a href="{{ route('register') }}" class="btn-brand text-sm">Sign Up</a>
            @endauth
        </div>

        <button class="lg:hidden" @click="mobileOpen = !mobileOpen">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <div x-show="mobileOpen" x-transition x-cloak class="max-h-[calc(100vh-4rem)] overflow-y-auto border-t border-border px-4 py-4 lg:hidden" x-data="{ openSection: null }">
        <div class="space-y-1">
            @foreach ($megaMenu as $label => $items)
                <div class="border-b border-border/60">
                    <button type="button" class="flex w-full items-center justify-between py-3 text-left text-sm font-medium" @click="openSection = openSection === '{{ $label }}' ? null : '{{ $label }}'">
                        {{ $label }}
                        <svg class="h-4 w-4 shrink-0 transition-transform" :class="openSection === '{{ $label }}' ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="openSection === '{{ $label }}'" x-cloak x-transition class="grid grid-cols-2 gap-1 pb-3 text-sm">
                        @foreach ($items as [$itemLabel, $href])
                            <a href="{{ $href }}" class="nav-link">{{ $itemLabel }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 flex gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-outline w-full text-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-outline w-full text-sm">Log In</a>
                <a href="{{ route('register') }}" class="btn-brand w-full text-sm">Sign Up</a>
            @endauth
        </div>
    </div>
</header>
