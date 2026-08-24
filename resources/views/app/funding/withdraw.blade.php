@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6" x-data="{ method: 'crypto' }">
    <h1 class="text-2xl font-bold">Withdraw</h1>

    <div class="glass-card p-6">
        <label class="label-field">How would you like to receive your funds?</label>
        <div class="mb-4 grid grid-cols-3 gap-2 sm:grid-cols-6">
            @foreach (['crypto' => 'Crypto', 'bank_transfer' => 'Bank Transfer', 'cashapp' => 'Cash App', 'venmo' => 'Venmo', 'paypal' => 'PayPal', 'other' => 'Other'] as $key => $label)
                <button type="button" @click="method = '{{ $key }}'" :class="method === '{{ $key }}' ? 'nav-link-active' : 'nav-link'" class="text-center text-xs">{{ $label }}</button>
            @endforeach
        </div>

        <form method="POST" action="{{ route('app.funding.withdraw.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="payment_method_type" x-bind:value="method">

            <div>
                <label class="label-field">Source wallet</label>
                <select name="wallet_type" class="input-field">
                    @foreach (['primary', 'trading', 'investment'] as $w)
                        <option value="{{ $w }}" @selected($selectedWallet === $w)>{{ ucfirst($w) }} Wallet</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-field">Asset / balance to withdraw from</label>
                <select name="asset_id" class="input-field">
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->symbol }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Crypto --}}
            <div x-show="method === 'crypto'" x-cloak class="space-y-4">
                <div>
                    <label class="label-field">Network</label>
                    <select name="network_id" class="input-field">
                        <option value="">N/A</option>
                        @foreach ($networks as $network)
                            <option value="{{ $network->id }}">{{ $network->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-field">Destination wallet address</label>
                    <input type="text" x-bind:name="method === 'crypto' ? 'address' : null" class="input-field" list="saved-addresses" placeholder="0x... or wallet address">
                    <datalist id="saved-addresses">
                        @foreach ($addresses as $addr)
                            <option value="{{ $addr->address }}">{{ $addr->label }}</option>
                        @endforeach
                    </datalist>
                </div>
            </div>

            {{-- Bank transfer --}}
            <div x-show="method === 'bank_transfer'" x-cloak class="space-y-4">
                <div>
                    <label class="label-field">Bank account number</label>
                    <input type="text" x-bind:name="method === 'bank_transfer' ? 'address' : null" class="input-field" placeholder="Account number">
                </div>
                <div>
                    <label class="label-field">Bank name, routing/SWIFT code, account holder name</label>
                    <textarea x-bind:name="method === 'bank_transfer' ? 'destination_details' : null" class="input-field" rows="2" placeholder="e.g. Chase Bank, Routing 021000021, Account holder: Jane Doe"></textarea>
                </div>
            </div>

            {{-- Cash App --}}
            <div x-show="method === 'cashapp'" x-cloak>
                <label class="label-field">Your $Cashtag</label>
                <input type="text" x-bind:name="method === 'cashapp' ? 'address' : null" class="input-field" placeholder="$YourCashtag">
            </div>

            {{-- Venmo --}}
            <div x-show="method === 'venmo'" x-cloak>
                <label class="label-field">Your Venmo username</label>
                <input type="text" x-bind:name="method === 'venmo' ? 'address' : null" class="input-field" placeholder="@your-venmo">
            </div>

            {{-- PayPal --}}
            <div x-show="method === 'paypal'" x-cloak>
                <label class="label-field">Your PayPal email</label>
                <input type="email" x-bind:name="method === 'paypal' ? 'address' : null" class="input-field" placeholder="you@example.com">
            </div>

            {{-- Other --}}
            <div x-show="method === 'other'" x-cloak class="space-y-4">
                <div>
                    <label class="label-field">Destination (account / handle / address)</label>
                    <input type="text" x-bind:name="method === 'other' ? 'address' : null" class="input-field">
                </div>
                <div>
                    <label class="label-field">Additional details</label>
                    <textarea x-bind:name="method === 'other' ? 'destination_details' : null" class="input-field" rows="2"></textarea>
                </div>
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
