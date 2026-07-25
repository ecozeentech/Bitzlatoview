<footer class="mt-20 border-t border-border bg-surface">
    <div class="page-shell grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand font-extrabold text-background">BZ</span>
                <span class="text-lg font-extrabold">Bitzlatoview</span>
            </div>
            <p class="mt-4 max-w-sm text-sm text-muted">Simulation-first multi-asset platform for crypto, stocks, forex, futures, P2P, and Web3 tools. Not financial advice. Trading involves risk of loss.</p>
        </div>
        @foreach([
            'Products' => [['Spot','markets'],['P2P','p2p'],['Futures','futures'],['Mining','mining'],['AI Bots','ai-trading-bot']],
            'Company' => [['About','about'],['Contact','contact'],['Security','security'],['Fees','fees'],['Careers','about']],
            'Legal' => [['Terms','terms'],['Privacy','privacy'],['Risk','risk-disclosure'],['AML/KYC','aml-kyc-policy'],['Cookies','cookie-policy']],
        ] as $title => $links)
        <div>
            <h4 class="text-sm font-semibold text-slate-100">{{ $title }}</h4>
            <ul class="mt-4 space-y-2">
                @foreach($links as [$label, $route])
                    <li><a class="text-sm text-muted hover:text-brand" href="{{ Route::has($route) ? route($route) : '#' }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
    <div class="border-t border-border">
        <div class="page-shell flex flex-col gap-2 py-6 text-xs text-muted md:flex-row md:items-center md:justify-between">
            <p>© {{ date('Y') }} Bitzlatoview. Paper-trading MVP. Connect licensed providers before live funds.</p>
            <p>Proof of Reserves · API Docs · Support</p>
        </div>
    </div>
</footer>
