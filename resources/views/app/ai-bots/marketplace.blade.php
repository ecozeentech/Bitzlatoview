@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">AI Trading Bot Marketplace</h1>
        <a href="{{ route('app.ai-bots.my-bots') }}" class="btn-outline text-sm">My Bots</a>
    </div>
    <div class="risk-banner">AI trading bots are experimental and may lose money. Bitzlatoview never guarantees returns. Bots run on Bitzlatoview's own internal engine, not a live connection to an external exchange.</div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($bots as $bot)
            <div class="glass-card p-5">
                <p class="pill-info">{{ ucfirst($bot->strategy_type) }}</p>
                <h2 class="mt-2 font-semibold">{{ $bot->name }}</h2>
                <p class="mt-1 text-xs text-text-muted">{{ implode(', ', $bot->supported_assets ?? []) }}</p>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <div>Return: <x-price-change :value="$bot->historical_return_pct" /></div>
                    <div class="text-text-muted">Max DD: {{ $bot->max_drawdown_pct }}%</div>
                    <div class="text-text-muted">Risk: {{ $bot->risk_score }}/100</div>
                    <div class="text-text-muted">Min: ${{ number_format($bot->min_allocation, 0) }}</div>
                </div>
                <a href="{{ route('app.ai-bots.show', $bot) }}" class="btn-brand mt-4 block text-center text-sm">View &amp; Allocate</a>
            </div>
        @endforeach
    </div>
</div>
@endsection
