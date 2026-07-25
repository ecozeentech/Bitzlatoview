@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Deposit</h1>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('app.funding.deposit.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label-field">Destination wallet</label>
                <select name="wallet_type" class="input-field">
                    @foreach (['primary', 'trading', 'investment'] as $w)
                        <option value="{{ $w }}" @selected($selectedWallet === $w)>{{ ucfirst($w) }} Wallet</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-field">Asset</label>
                <select name="asset_id" class="input-field">
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->symbol }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-field">Network</label>
                <select name="network_id" class="input-field">
                    <option value="">N/A</option>
                    @foreach ($networks as $network)
                        <option value="{{ $network->id }}">{{ $network->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-danger">Always double-check the network. Sending on the wrong network can result in permanent loss of funds.</p>
            </div>

            <div class="rounded-lg border border-border bg-surface-2 p-4 text-center">
                <p class="text-xs uppercase text-text-muted">Simulated deposit address</p>
                <p class="mt-1 break-all font-numeric text-sm">{{ $depositAddress }}</p>
                <p class="mt-2 text-xs text-text-muted">Minimum deposit: 0.0001 · Confirmations required: 1–20 depending on network</p>
            </div>

            <div>
                <label class="label-field">Amount to simulate receiving</label>
                <input type="number" step="0.00000001" name="amount" class="input-field" required>
                <p class="mt-1 text-xs text-text-muted">This build simulates on-chain confirmation instantly. A production deployment would credit balances only after real network confirmations via a custody/webhook provider.</p>
            </div>

            <div>
                <label class="label-field">Funding note (optional)</label>
                <input type="text" name="note" class="input-field" placeholder="e.g. Salary top-up">
            </div>

            <button class="btn-brand w-full">Simulate Deposit</button>
        </form>
    </div>

    <div class="risk-banner">Deposits, withdrawals and balances in this build are part of a paper-trading simulation. No real cryptocurrency is transferred.</div>
</div>
@endsection
