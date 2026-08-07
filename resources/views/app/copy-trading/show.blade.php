@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="glass-card p-6">
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-gradient text-xl font-bold text-background">{{ substr($trader->display_name, 0, 1) }}</span>
            <div>
                <h1 class="text-xl font-bold">{{ $trader->display_name }} @if ($trader->is_verified)<span class="pill-info">Verified</span>@endif</h1>
                <p class="text-sm text-text-muted">{{ ucfirst($trader->category) }} trader · {{ number_format($trader->followers_count) }} followers</p>
            </div>
        </div>
        <p class="mt-4 text-sm text-text-muted">{{ $trader->bio }}</p>
        <p class="mt-2 text-sm"><strong>Strategy:</strong> {{ $trader->strategy }}</p>

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">30d</p><x-price-change :value="$trader->return_30d_pct" /></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">90d</p><x-price-change :value="$trader->return_90d_pct" /></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Max DD</p><p class="font-numeric">{{ $trader->max_drawdown_pct }}%</p></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Win rate</p><p class="font-numeric">{{ $trader->win_rate_pct }}%</p></div>
        </div>
    </div>

    <div class="risk-banner">Copy trading can amplify gains and losses. Past performance does not guarantee future results. No returns are guaranteed, and you can lose your allocated amount.</div>

    @if ($myAllocation)
        <div class="glass-card p-6">
            <h2 class="mb-2 font-semibold">Your Allocation</h2>
            <p class="text-sm">Amount: ${{ number_format($myAllocation->amount, 2) }} · Status: <span class="pill-warning">{{ $myAllocation->status }}</span> · P&amp;L: <span class="{{ $myAllocation->pnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($myAllocation->pnl, 2) }}</span></p>
            <div class="mt-3 flex gap-2">
                @if ($myAllocation->status === 'active')
                    <form method="POST" action="{{ route('app.copy-trading.pause', $myAllocation) }}">@csrf<button class="btn-outline text-sm">Pause</button></form>
                @else
                    <form method="POST" action="{{ route('app.copy-trading.resume', $myAllocation) }}">@csrf<button class="btn-outline text-sm">Resume</button></form>
                @endif
                <form method="POST" action="{{ route('app.copy-trading.stop', $myAllocation) }}">@csrf<button class="text-sm text-danger hover:underline">Stop &amp; Settle</button></form>
            </div>
        </div>
    @else
        <div class="glass-card p-6">
            <h2 class="mb-3 font-semibold">Allocate from Investment Wallet</h2>
            <form method="POST" action="{{ route('app.copy-trading.allocate', $trader) }}" class="grid gap-3 sm:grid-cols-2">
                @csrf
                <div><label class="label-field">Amount (USDT)</label><input type="number" step="0.01" name="amount" class="input-field" required></div>
                <div><label class="label-field">Copy ratio</label><input type="number" step="0.1" name="copy_ratio" value="1" class="input-field"></div>
                <div><label class="label-field">Stop loss %</label><input type="number" step="0.1" name="stop_loss_pct" class="input-field"></div>
                <div><label class="label-field">Take profit %</label><input type="number" step="0.1" name="take_profit_pct" class="input-field"></div>
                <div class="sm:col-span-2"><label class="label-field">Max position size (USDT)</label><input type="number" step="0.01" name="max_position_size" class="input-field"></div>
                <button class="btn-brand sm:col-span-2">Start Copying</button>
            </form>
        </div>
    @endif
</div>
@endsection
