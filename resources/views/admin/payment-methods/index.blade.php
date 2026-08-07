@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Payment Settings</h1>
    <div class="risk-banner">
        These are the company's receiving accounts shown to users on the Deposit page. Every deposit is still reviewed manually by an admin (with uploaded proof of payment) before any funds are credited to a user's wallet — nothing here automates a real money transfer.
    </div>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Add Payment Method</h2>
        <form method="POST" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data" class="grid gap-3 sm:grid-cols-2">
            @csrf
            <div>
                <label class="label-field">Display name</label>
                <input type="text" name="name" class="input-field" placeholder="e.g. USDT (TRC20) or Chase Business Checking" required>
            </div>
            <div>
                <label class="label-field">Type</label>
                <select name="type" class="input-field">
                    <option value="crypto">Crypto</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cashapp">Cash App</option>
                    <option value="venmo">Venmo</option>
                    <option value="paypal">PayPal</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="label-field">Currency / asset</label>
                <input type="text" name="currency" class="input-field" placeholder="USDT, USD, EUR..." value="USDT" required>
            </div>
            <div>
                <label class="label-field">Network (crypto only)</label>
                <input type="text" name="network" class="input-field" placeholder="TRC20, ERC20, BEP20...">
            </div>
            <div>
                <label class="label-field">Address / account number / handle</label>
                <input type="text" name="address" class="input-field" placeholder="Wallet address, account number, $cashtag...">
            </div>
            <div>
                <label class="label-field">Memo / reference tag (if required)</label>
                <input type="text" name="memo" class="input-field">
            </div>
            <div>
                <label class="label-field">Minimum amount</label>
                <input type="number" step="0.01" name="min_amount" class="input-field" value="10" required>
            </div>
            <div>
                <label class="label-field">Maximum amount (optional)</label>
                <input type="number" step="0.01" name="max_amount" class="input-field">
            </div>
            <div>
                <label class="label-field">QR code image (optional)</label>
                <input type="file" name="qr_code" accept="image/*" class="input-field">
            </div>
            <div>
                <label class="label-field">Sort order</label>
                <input type="number" name="sort_order" class="input-field" value="0">
            </div>
            <div class="sm:col-span-2">
                <label class="label-field">Instructions shown to the depositor</label>
                <textarea name="instructions" class="input-field" rows="3" placeholder="e.g. Send only USDT on the TRC20 network to this address. Include your reference code in the transfer memo if your bank allows it." required></textarea>
            </div>
            <button class="btn-brand sm:col-span-2">Add Payment Method</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Type</th><th>Currency</th><th>Limits</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($methods as $method)
                    <tr>
                        <td>
                            {{ $method->label() }}
                            @if ($method->qr_code_path)
                                <img src="{{ asset('storage/'.$method->qr_code_path) }}" class="mt-1 h-10 w-10 rounded border border-border">
                            @endif
                        </td>
                        <td>{{ str_replace('_', ' ', $method->type) }}</td>
                        <td>{{ $method->currency }}</td>
                        <td class="text-text-muted">{{ number_format($method->min_amount, 2) }}{{ $method->max_amount ? ' – '.number_format($method->max_amount, 2) : '+' }}</td>
                        <td><span class="pill-{{ $method->is_active ? 'success' : 'muted' }}">{{ $method->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="space-x-2">
                            <form method="POST" action="{{ route('admin.payment-methods.toggle', $method) }}" class="inline">@csrf<button class="text-xs text-brand hover:underline">{{ $method->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                            <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}" class="inline">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No payment methods configured yet — users won't be able to deposit until you add at least one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
