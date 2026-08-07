<footer class="border-t border-border bg-surface/40">
    <div class="mx-auto max-w-7xl px-4 py-14 lg:px-8">
        <div class="grid grid-cols-2 gap-8 md:grid-cols-4 lg:grid-cols-6">
            <div class="col-span-2">
                <a href="{{ url('/') }}" class="flex items-center gap-2 text-lg font-extrabold">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-gradient text-background">B</span>
                    Bitzlato<span class="text-brand">view</span>
                </a>
                <p class="mt-3 max-w-xs text-sm text-text-muted">One dashboard for digital and global markets — crypto, stocks, forex, futures and NFTs.</p>
            </div>
            @php
                $footerCols = [
                    'Products' => [['Spot Trading','/crypto'],['Crypto Swap','/swap'],['P2P Trading','/p2p'],['Futures','/futures'],['Copy Trading','/copy-trading'],['AI Trading Bots','/ai-trading-bot'],['Mining','/mining'],['NFT Marketplace','/nft']],
                    'Markets' => [['All Markets','/markets'],['Top Gainers','/markets/top-gainers'],['Top Losers','/markets/top-losers'],['New Listings','/markets/new-listings'],['Stocks','/stocks'],['Forex','/forex'],['MetaTrader 5','/metatrader-5']],
                    'Company' => [['About','/about'],['Contact','/contact'],['Security','/security'],['Fees','/fees'],['Proof of Reserves','/proof-of-reserves'],['API Docs','/api-docs'],['Affiliate','/affiliate']],
                    'Legal' => [['Terms of Service','/terms'],['Privacy Policy','/privacy'],['Risk Disclosure','/risk-disclosure'],['AML / KYC Policy','/aml-kyc-policy'],['Cookie Policy','/cookie-policy']],
                ];
            @endphp
            @foreach ($footerCols as $heading => $links)
                <div>
                    <h4 class="text-sm font-semibold text-text-main">{{ $heading }}</h4>
                    <ul class="mt-3 space-y-2 text-sm text-text-muted">
                        @foreach ($links as [$label, $href])
                            <li><a href="{{ $href }}" class="hover:text-brand">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="mt-10 rounded-xl border border-border bg-surface-2/60 p-4 text-xs leading-relaxed text-text-muted">
            <strong class="text-text-main">Risk disclosure:</strong> Trading crypto, stocks, forex, futures and derivatives involves significant risk and may not be suitable for all investors. Past performance does not guarantee future results. Bitzlatoview does not promise or guarantee any returns. Stock and forex trading, and virtual cards, remain in a disclosed paper/non-live state until licensed broker, market-data, and card-issuing providers are connected. Nothing on this site is tax, legal, or investment advice. The "CFA"-style analyst packages offered here use verified-only credentials and are not affiliated with or endorsed by CFA Institute unless explicitly stated.
        </div>

        <div class="mt-8 flex flex-col items-center justify-between gap-4 border-t border-border pt-6 text-xs text-text-muted md:flex-row">
            <p>&copy; {{ date('Y') }} Bitzlatoview. All rights reserved.</p>
            <p>Bitzlatoview is not currently licensed as a money transmitter, broker-dealer, or exchange in every jurisdiction unless explicitly stated for that region. Availability of certain features may vary by location.</p>
        </div>
    </div>
</footer>
