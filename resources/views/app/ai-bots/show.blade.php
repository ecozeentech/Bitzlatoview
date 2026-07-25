@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="glass-card p-6">
        <p class="pill-info">{{ ucfirst($bot->strategy_type) }} strategy</p>
        <h1 class="mt-2 text-xl font-bold">{{ $bot->name }}</h1>
        <p class="mt-2 text-sm text-text-muted">{{ $bot->description }}</p>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Return</p><x-price-change :value="$bot->historical_return_pct" /></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Max DD</p><p class="font-numeric">{{ $bot->max_drawdown_pct }}%</p></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Risk</p><p class="font-numeric">{{ $bot->risk_score }}/100</p></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Lock</p><p class="font-numeric">{{ $bot->lock_days }}d</p></div>
        </div>
    </div>

    <div class="risk-banner">AI trading bots are experimental and may lose money. No guaranteed returns.</div>

    @if ($myAllocation)
        <div class="glass-card p-6">
            <h2 class="mb-2 font-semibold">Your Allocation</h2>
            <p class="text-sm">Amount: ${{ number_format($myAllocation->amount, 2) }} · Status: <span class="pill-warning">{{ $myAllocation->status }}</span></p>
            @if ($myAllocation->unlocks_at)
                <p class="text-xs text-text-muted">Unlocks: {{ $myAllocation->unlocks_at->format('M d, Y') }}</p>
            @endif
            <div class="mt-3 flex gap-2">
                @if ($myAllocation->status === 'active')
                    <form method="POST" action="{{ route('app.ai-bots.pause', $myAllocation) }}">@csrf<button class="btn-outline text-sm">Pause</button></form>
                @else
                    <form method="POST" action="{{ route('app.ai-bots.resume', $myAllocation) }}">@csrf<button class="btn-outline text-sm">Resume</button></form>
                @endif
                <form method="POST" action="{{ route('app.ai-bots.stop', $myAllocation) }}">@csrf<button class="text-sm text-danger hover:underline">Stop &amp; Settle</button></form>
            </div>
        </div>
    @else
        <div class="glass-card p-6">
            <h2 class="mb-3 font-semibold">Allocate from Investment Wallet</h2>
            <form method="POST" action="{{ route('app.ai-bots.allocate', $bot) }}" class="space-y-3">
                @csrf
                <label class="label-field">Amount (USDT, min ${{ $bot->min_allocation }})</label>
                <input type="number" step="0.01" name="amount" min="{{ $bot->min_allocation }}" class="input-field" required>
                <button class="btn-brand w-full">Start Bot</button>
            </form>
        </div>
    @endif
</div>
@endsection
