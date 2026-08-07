@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Crypto Mining</h1>
        <div class="flex gap-2 text-sm">
            <a href="{{ route('app.mining.contracts') }}" class="btn-outline">My Contracts</a>
            <a href="{{ route('app.mining.rewards') }}" class="btn-outline">Rewards</a>
        </div>
    </div>

    <div class="risk-banner">Mining rewards follow the disclosed reward rate and are not guaranteed. Real mining profitability depends on network difficulty and coin price, and can go to zero.</div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($packages as $package)
            <div class="glass-card p-5">
                <div class="flex items-center gap-2">
                    <x-asset-icon :symbol="$package->asset->symbol" />
                    <h2 class="font-semibold">{{ $package->name }}</h2>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-text-muted">
                    <div>Hashrate: <span class="text-text-main">{{ $package->hashrate_th }} TH/s</span></div>
                    <div>Term: <span class="text-text-main">{{ $package->term_days }}d</span></div>
                    <div>Maintenance fee: <span class="text-text-main">{{ $package->maintenance_fee_pct }}%</span></div>
                    <div>Daily est.: <span class="text-text-main">{{ $package->estimated_daily_reward_pct }}%</span></div>
                </div>
                <p class="mt-2 font-numeric text-lg font-bold">${{ number_format($package->price, 0) }}</p>
                <form method="POST" action="{{ route('app.mining.purchase', $package) }}" class="mt-3 space-y-2">
                    @csrf
                    <select name="reward_destination" class="input-field text-sm">
                        <option value="investment">Reward → Investment Wallet</option>
                        <option value="primary">Reward → Primary Wallet</option>
                    </select>
                    <button class="btn-brand w-full text-sm">Purchase Contract</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
