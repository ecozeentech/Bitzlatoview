@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Deposit</h1>

    @if ($paymentMethods->isEmpty())
        <div class="risk-banner">No payment methods are configured yet. Please contact support — an administrator needs to add at least one receiving account before deposits can be made.</div>
    @endif

    <div class="glass-card p-6" x-data="{ methodId: '{{ old('payment_method_id', $paymentMethods->first()->id ?? '') }}', methods: {{ $paymentMethods->keyBy('id')->toJson() }} }">
        <form method="POST" action="{{ route('app.funding.deposit.store') }}" enctype="multipart/form-data" class="space-y-4">
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
                <label class="label-field">Asset to credit</label>
                <select name="asset_id" class="input-field">
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->symbol }} — {{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label-field">Payment method</label>
                <select name="payment_method_id" x-model="methodId" class="input-field">
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method->id }}">{{ $method->label() }} ({{ $method->currency }})</option>
                    @endforeach
                </select>
            </div>

            <template x-if="methodId && methods[methodId]">
                <div class="rounded-lg border border-border bg-surface-2 p-4 text-sm">
                    <p class="font-medium" x-text="methods[methodId] ? (methods[methodId].name + (methods[methodId].network ? ' (' + methods[methodId].network + ')' : '')) : ''"></p>
                    <template x-if="methods[methodId] && methods[methodId].address">
                        <p class="mt-2 break-all font-numeric" x-text="'Address / account: ' + methods[methodId].address"></p>
                    </template>
                    <template x-if="methods[methodId] && methods[methodId].memo">
                        <p class="mt-1 font-numeric" x-text="'Memo / reference: ' + methods[methodId].memo"></p>
                    </template>
                    <template x-if="methods[methodId] && methods[methodId].qr_code_path">
                        <img :src="'/storage/' + (methods[methodId] ? methods[methodId].qr_code_path : '')" class="mt-2 h-32 w-32 rounded border border-border">
                    </template>
                    <p class="mt-2 whitespace-pre-line text-text-muted" x-text="methods[methodId] ? methods[methodId].instructions : ''"></p>
                    <p class="mt-2 text-xs text-text-muted" x-text="methods[methodId] ? ('Min: ' + methods[methodId].min_amount + (methods[methodId].max_amount ? ' · Max: ' + methods[methodId].max_amount : '')) : ''"></p>
                </div>
            </template>

            <div>
                <label class="label-field">Amount sent</label>
                <input type="number" step="0.00000001" name="amount" class="input-field" required>
            </div>

            <div>
                <label class="label-field">Proof of payment (screenshot or receipt — JPG, PNG or PDF, max 5MB)</label>
                <input type="file" name="proof_file" accept="image/*,.pdf" class="input-field" required>
            </div>

            <div>
                <label class="label-field">Note (optional)</label>
                <input type="text" name="note" class="input-field" placeholder="Anything our team should know about this payment">
            </div>

            <div class="risk-banner">Deposits are credited only after an administrator manually verifies your proof of payment against the selected payment method. This can take time — funds are not credited automatically.</div>

            <button class="btn-brand w-full" @if($paymentMethods->isEmpty()) disabled @endif>Submit Deposit Request</button>
        </form>
    </div>
</div>
@endsection
