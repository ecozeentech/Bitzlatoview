@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Copy Trading — Trader Directory</h1>
        <a href="{{ route('app.copy-trading.my-copies') }}" class="btn-outline text-sm">My Copies</a>
    </div>

    <div class="flex gap-2 text-sm">
        @foreach (['all' => 'All', 'crypto' => 'Crypto', 'forex' => 'Forex', 'futures' => 'Futures', 'stock' => 'Stocks', 'p2p' => 'P2P'] as $key => $label)
            <a href="{{ route('app.copy-trading.traders', ['category' => $key]) }}" class="{{ $category === $key ? 'nav-link-active' : 'nav-link' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($traders as $trader)
            <div class="glass-card p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-gradient font-bold text-background">{{ substr($trader->display_name, 0, 1) }}</span>
                    <div>
                        <p class="font-semibold">{{ $trader->display_name }} @if ($trader->is_verified)<span class="pill-info">Verified</span>@endif</p>
                        <p class="text-xs text-text-muted">{{ ucfirst($trader->category) }} · Risk {{ $trader->risk_score }}/100</p>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <div>30d return: <x-price-change :value="$trader->return_30d_pct" /></div>
                    <div>90d return: <x-price-change :value="$trader->return_90d_pct" /></div>
                    <div class="text-text-muted">Max drawdown: {{ $trader->max_drawdown_pct }}%</div>
                    <div class="text-text-muted">Followers: {{ number_format($trader->followers_count) }}</div>
                </div>
                <a href="{{ route('app.copy-trading.traders.show', $trader) }}" class="btn-brand mt-4 block text-center text-sm">View Profile</a>
            </div>
        @endforeach
    </div>

    <div class="risk-banner">Copy trading can amplify gains and losses. Past performance does not guarantee future results. No returns are guaranteed.</div>
</div>
@endsection
