@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <h1 class="text-2xl font-bold">Withdrawal Fee</h1>
    <p class="text-sm text-text-muted">Charged against every withdrawal request at submission time — the fee is carved out of the requested amount (not billed separately), and the net amount is what actually gets sent to the user. Fee revenue is credited to the platform's Primary Wallet.</p>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('admin.settings.withdrawal-fee.update') }}" class="space-y-4">
            @csrf
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="enabled" value="1" @checked($settings['enabled']) class="rounded border-border bg-surface-2 text-brand focus:ring-brand">
                Enable withdrawal fee
            </label>
            <div>
                <label class="label-field">Fee percentage (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="percentage" class="input-field" value="{{ old('percentage', $settings['percentage']) }}" required>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="label-field">Minimum fee amount (optional)</label>
                    <input type="number" step="0.00000001" min="0" name="min_fee" class="input-field" value="{{ old('min_fee', $settings['min_fee']) }}" placeholder="No minimum">
                </div>
                <div>
                    <label class="label-field">Maximum fee amount (optional)</label>
                    <input type="number" step="0.00000001" min="0" name="max_fee" class="input-field" value="{{ old('max_fee', $settings['max_fee']) }}" placeholder="No maximum">
                </div>
            </div>
            <p class="text-xs text-text-muted">Min/max are denominated in the same asset as each withdrawal (e.g. a max of 10 applies as 10 BTC to a BTC withdrawal and 10 USDT to a USDT withdrawal). The fee never exceeds the withdrawal amount itself.</p>
            <button class="btn-brand text-sm">Save Settings</button>
        </form>
    </div>
</div>
@endsection
