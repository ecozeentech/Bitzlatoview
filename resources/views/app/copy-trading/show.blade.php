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

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">30d</p><x-price-change :value="$trader->return_30d_pct" /></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">90d</p><x-price-change :value="$trader->return_90d_pct" /></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Max DD</p><p class="font-numeric">{{ $trader->max_drawdown_pct }}%</p></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Win rate</p><p class="font-numeric">{{ $trader->win_rate_pct }}%</p></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Return period</p><p class="font-numeric">{{ $trader->lock_days }}d</p></div>
        </div>
    </div>

    <div class="risk-banner">Copy trading can amplify gains and losses. Past performance does not guarantee future results. No returns are guaranteed, and you can lose your allocated amount.</div>

    @if ($myAllocation)
        <div class="glass-card p-6">
            <h2 class="mb-2 font-semibold">Your Allocation</h2>
            <p class="text-sm">Amount: ${{ number_format($myAllocation->amount, 2) }} · Status: <span class="pill-warning">{{ $myAllocation->status }}</span> · P&amp;L: <span class="{{ $myAllocation->pnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($myAllocation->pnl, 2) }}</span></p>
            @if ($myAllocation->unlocks_at)
                <p class="mt-1 text-xs text-text-muted">{{ $myAllocation->unlocks_at->isFuture() ? 'Unlocks '.$myAllocation->unlocks_at->format('M d, Y').' — funds can be settled and withdrawn after this date.' : 'Unlocked — ready to stop and settle.' }}</p>
            @endif
            <div class="mt-3 flex gap-2">
                @if ($myAllocation->status === 'active')
                    <form method="POST" action="{{ route('app.copy-trading.pause', $myAllocation) }}">@csrf<button class="btn-outline text-sm">Pause</button></form>
                @else
                    <form method="POST" action="{{ route('app.copy-trading.resume', $myAllocation) }}">@csrf<button class="btn-outline text-sm">Resume</button></form>
                @endif
                <form method="POST" action="{{ route('app.copy-trading.stop', $myAllocation) }}">@csrf<button class="text-sm text-danger hover:underline">Stop &amp; Settle</button></form>
            </div>
        </div>
    @elseif ($trader->status !== 'active')
        <div class="glass-card p-6 text-center">
            <span class="pill-warning">{{ $trader->status === 'sold_out' ? 'Sold Out' : ucfirst(str_replace('_', ' ', $trader->status)) }}</span>
            <p class="mt-2 text-sm text-text-muted">This trader isn't accepting new copiers right now. Check back later.</p>
        </div>
    @else
        <div class="glass-card p-6">
            <h2 class="mb-3 font-semibold">Copy from Primary Wallet</h2>
            <form method="POST" action="{{ route('app.copy-trading.allocate', $trader) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="label-field">Amount (USDT)</label>
                    <input type="number" step="0.01" name="amount" min="{{ $trader->min_copy_amount }}" class="input-field" required>
                    <p class="mt-1 text-xs text-text-muted">Minimum ${{ number_format($trader->min_copy_amount, 2) }} to copy {{ $trader->display_name }}.</p>
                </div>
                <button class="btn-brand w-full">Start Copying</button>
            </form>
        </div>
    @endif
</div>
@endsection
