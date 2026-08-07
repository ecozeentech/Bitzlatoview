@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Withdraw</h1>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('app.funding.withdraw.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label-field">Source wallet</label>
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
                <label class="label-field">Network (crypto only)</label>
                <select name="network_id" class="input-field">
                    <option value="">N/A</option>
                    @foreach ($networks as $network)
                        <option value="{{ $network->id }}">{{ $network->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-field">How should we send your funds?</label>
                <select name="payment_method_type" class="input-field">
                    <option value="crypto">Crypto transfer</option>
                    <option value="bank_transfer">Bank transfer</option>
                    <option value="cashapp">Cash App</option>
                    <option value="venmo">Venmo</option>
                    <option value="paypal">PayPal</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="label-field">Destination address / account</label>
                <input type="text" name="address" class="input-field" list="saved-addresses" required>
                <datalist id="saved-addresses">
                    @foreach ($addresses as $addr)
                        <option value="{{ $addr->address }}">{{ $addr->label }}</option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="label-field">Additional destination details (bank name, routing/SWIFT, account holder name, etc.)</label>
                <textarea name="destination_details" class="input-field" rows="2"></textarea>
            </div>
            <div>
                <label class="label-field">Amount</label>
                <input type="number" step="0.00000001" name="amount" class="input-field" required>
                <p class="mt-1 text-xs text-text-muted">A network/processing fee of 0.1% applies. Funds are locked in your wallet immediately and released only once an administrator confirms the external transfer was sent.</p>
            </div>
            <div>
                <label class="label-field">Funding note (optional)</label>
                <input type="text" name="note" class="input-field">
            </div>

            <div class="risk-banner">Every withdrawal requires manual review and confirmation by an administrator before funds are sent externally. This is a deliberate compliance control, not an automated payout — please allow processing time.</div>

            <button class="btn-brand w-full">Request Withdrawal</button>
        </form>
    </div>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Save a withdrawal address for next time</h2>
        <form method="POST" action="{{ route('app.funding.address-book.store') }}" class="grid gap-3 sm:grid-cols-2">
            @csrf
            <select name="asset_id" class="input-field">
                @foreach ($assets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->symbol }}</option>
                @endforeach
            </select>
            <select name="network_id" class="input-field">
                <option value="">N/A</option>
                @foreach ($networks as $network)
                    <option value="{{ $network->id }}">{{ $network->name }}</option>
                @endforeach
            </select>
            <input type="text" name="address" class="input-field sm:col-span-2" placeholder="Address" required>
            <input type="text" name="label" class="input-field sm:col-span-2" placeholder="Label (e.g. My Ledger)">
            <button class="btn-outline sm:col-span-2">Save Address</button>
        </form>
    </div>
</div>
@endsection
