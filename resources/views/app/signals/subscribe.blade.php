@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="glass-card p-6">
        <span class="pill-{{ $package->risk_level === 'high' ? 'danger' : ($package->risk_level === 'low' ? 'success' : 'info') }}">{{ ucfirst($package->risk_level) }} risk</span>
        <h1 class="mt-2 text-xl font-bold">{{ $package->name }}</h1>
        <p class="mt-2 text-sm text-text-muted">{{ $package->description }}</p>

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Expected Return</p><x-price-change :value="$package->expected_return_pct" /></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Duration</p><p class="font-numeric">{{ $package->duration_days }}d</p></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Fee</p><p class="font-numeric">{{ $package->fee_pct }}%</p></div>
            <div class="rounded-lg bg-surface-2 p-3 text-center"><p class="text-xs text-text-muted">Tracks</p><p class="font-numeric">{{ $package->tracked_asset_symbol }}</p></div>
        </div>
    </div>

    <div class="risk-banner">Expected return is a disclosed estimate, not a guarantee — you can lose some or all of your allocated amount. Past performance does not guarantee future results.</div>

    @if ($myAllocation)
        <div class="glass-card p-6">
            <h2 class="mb-2 font-semibold">Your Subscription</h2>
            <p class="text-sm">Amount: ${{ number_format($myAllocation->amount, 2) }} · Status: <span class="pill-warning">{{ $myAllocation->status }}</span> · P&amp;L: <span class="{{ $myAllocation->pnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($myAllocation->pnl, 2) }}</span></p>
            <div class="mt-3 flex gap-2">
                @if ($myAllocation->status === 'active')
                    <form method="POST" action="{{ route('app.signals.pause', $myAllocation) }}">@csrf<button class="btn-outline text-sm">Pause</button></form>
                @else
                    <form method="POST" action="{{ route('app.signals.resume', $myAllocation) }}">@csrf<button class="btn-outline text-sm">Resume</button></form>
                @endif
                <form method="POST" action="{{ route('app.signals.stop', $myAllocation) }}">@csrf<button class="text-sm text-danger hover:underline">Stop &amp; Settle</button></form>
            </div>
        </div>
    @else
        <div class="glass-card p-6">
            <h2 class="mb-3 font-semibold">Subscribe from Investment Wallet</h2>
            <form method="POST" action="{{ route('app.signals.subscribe', $package) }}" class="grid gap-3 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label class="label-field">Amount (USDT)</label>
                    <input type="number" step="0.01" name="amount" min="{{ $package->min_investment }}" @if($package->max_investment) max="{{ $package->max_investment }}" @endif class="input-field" required>
                    <p class="mt-1 text-xs text-text-muted">Min ${{ number_format($package->min_investment, 2) }}{{ $package->max_investment ? ' – max $'.number_format($package->max_investment, 2) : '' }}.</p>
                </div>
                <button class="btn-brand sm:col-span-2">Subscribe</button>
            </form>
        </div>
    @endif
</div>
@endsection
