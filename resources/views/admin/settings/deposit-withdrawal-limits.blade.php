@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <h1 class="text-2xl font-bold">Deposit & Withdrawal Limits</h1>
    <p class="text-sm text-text-muted">Platform-wide floors and ceilings applied to every user's deposit and withdrawal amount, on top of any per-payment-method min/max already set on <a href="{{ route('admin.payment-methods.index') }}" class="text-brand hover:underline">Payment Settings</a> for deposits. Leave a field blank for no limit on that side.</p>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('admin.settings.deposit-withdrawal-limits.update') }}" class="space-y-5">
            @csrf
            <div>
                <h2 class="mb-2 text-sm font-semibold">Deposits</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="label-field">Minimum deposit amount</label>
                        <input type="number" step="0.00000001" min="0" name="deposit_min" class="input-field" value="{{ old('deposit_min', $settings['deposit_min']) }}" placeholder="No minimum">
                    </div>
                    <div>
                        <label class="label-field">Maximum deposit amount</label>
                        <input type="number" step="0.00000001" min="0" name="deposit_max" class="input-field" value="{{ old('deposit_max', $settings['deposit_max']) }}" placeholder="No maximum">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-2 text-sm font-semibold">Withdrawals</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="label-field">Minimum withdrawal amount</label>
                        <input type="number" step="0.00000001" min="0" name="withdrawal_min" class="input-field" value="{{ old('withdrawal_min', $settings['withdrawal_min']) }}" placeholder="No minimum">
                    </div>
                    <div>
                        <label class="label-field">Maximum withdrawal amount</label>
                        <input type="number" step="0.00000001" min="0" name="withdrawal_max" class="input-field" value="{{ old('withdrawal_max', $settings['withdrawal_max']) }}" placeholder="No maximum">
                    </div>
                </div>
            </div>

            <p class="text-xs text-text-muted">Amounts are compared directly against whatever a user enters, regardless of asset — these are a simple platform-wide sanity floor/ceiling, applied for every wallet and every asset alike.</p>
            <button class="btn-brand text-sm">Save Limits</button>
        </form>
    </div>
</div>
@endsection
