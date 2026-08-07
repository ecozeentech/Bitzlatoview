@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">Fees</h1>
    <p class="mt-2 text-text-muted">These are the platform's default fee rates and are configurable by the admin per market.</p>

    <div class="mt-8 grid gap-6 sm:grid-cols-2">
        <div class="glass-card p-5">
            <h3 class="font-semibold">Spot Trading</h3>
            <p class="mt-2 text-sm text-text-muted">Maker 0.10% · Taker 0.10%</p>
        </div>
        <div class="glass-card p-5">
            <h3 class="font-semibold">Crypto Swap</h3>
            <p class="mt-2 text-sm text-text-muted">0.5% spread-equivalent fee, shown before you confirm.</p>
        </div>
        <div class="glass-card p-5">
            <h3 class="font-semibold">P2P Trading</h3>
            <p class="mt-2 text-sm text-text-muted">No taker fee for buyers/sellers on P2P; merchants may pay an ad-listing fee in future versions.</p>
        </div>
        <div class="glass-card p-5">
            <h3 class="font-semibold">Withdrawals</h3>
            <p class="mt-2 text-sm text-text-muted">Network-dependent flat fee, shown at withdrawal time.</p>
        </div>
        <div class="glass-card p-5">
            <h3 class="font-semibold">Futures</h3>
            <p class="mt-2 text-sm text-text-muted">Maker 0.02% · Taker 0.05% · Funding rate applied per market.</p>
        </div>
        <div class="glass-card p-5">
            <h3 class="font-semibold">Mining / Investments</h3>
            <p class="mt-2 text-sm text-text-muted">Maintenance fee disclosed per mining package; no fee on flexible Earn products.</p>
        </div>
    </div>
</div>
@endsection
