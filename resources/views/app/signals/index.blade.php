@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Signals</h1>
    <div class="risk-banner">Signals are experimental and may lose money. Expected returns shown are disclosed estimates based on historical/backtested figures — not guarantees. Signals settle against real market price movement on Bitzlatoview's internal engine, not a live connection to an external exchange.</div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($packages as $package)
            <div class="glass-card p-5">
                <span class="pill-{{ $package->risk_level === 'high' ? 'danger' : ($package->risk_level === 'low' ? 'success' : 'info') }}">{{ ucfirst($package->risk_level) }} risk</span>
                <h2 class="mt-2 font-semibold">{{ $package->name }}</h2>
                <p class="mt-1 text-xs text-text-muted">{{ \Illuminate\Support\Str::limit($package->description, 90) }}</p>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <div>Expected return: <x-price-change :value="$package->expected_return_pct" /></div>
                    <div class="text-text-muted">Duration: {{ $package->duration_days }}d</div>
                    <div class="text-text-muted">Min: ${{ number_format($package->min_investment, 0) }}</div>
                    <div class="text-text-muted">Tracks: {{ $package->tracked_asset_symbol }}</div>
                </div>
                <a href="{{ route('app.signals.show', $package) }}" class="btn-brand mt-4 block text-center text-sm">View &amp; Subscribe</a>
            </div>
        @empty
            <p class="text-sm text-text-muted">No signal packages available right now.</p>
        @endforelse
    </div>

    <div class="glass-card overflow-x-auto">
        <h2 class="p-5 pb-0 font-semibold">My Subscriptions</h2>
        <div class="overflow-x-auto p-5 pt-3">
            <table class="data-table">
                <thead><tr><th>Package</th><th>Amount</th><th>P&amp;L</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($mySubscriptions as $s)
                        <tr>
                            <td><a href="{{ route('app.signals.show', $s->package) }}" class="text-brand hover:underline">{{ $s->package->name }}</a></td>
                            <td class="font-numeric">${{ number_format($s->amount, 2) }}</td>
                            <td class="font-numeric {{ $s->pnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($s->pnl, 2) }}</td>
                            <td><span class="pill-{{ $s->status === 'active' ? 'success' : ($s->status === 'stopped' ? 'muted' : 'warning') }}">{{ $s->status }}</span></td>
                            <td>
                                @if ($s->status !== 'stopped')
                                    <form method="POST" action="{{ route('app.signals.stop', $s) }}">@csrf<button class="text-xs text-danger hover:underline">Stop</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-text-muted">You have no signal subscriptions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
