@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Investment / Earn Products</h1>
    <div class="risk-banner">Investment products carry risk of loss. Rates are illustrative, not guaranteed.</div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($products as $product)
            <div class="glass-card p-5">
                <div class="flex items-center gap-2"><x-asset-icon :symbol="$product->asset->symbol" /><h2 class="font-semibold">{{ $product->name }}</h2></div>
                <p class="mt-2 font-numeric text-2xl font-bold price-up">{{ $product->apy_pct }}% APY</p>
                <p class="text-xs text-text-muted">{{ $product->lock_days > 0 ? $product->lock_days.'-day lock' : 'Flexible, no lock' }} · Min {{ $product->min_amount }} {{ $product->asset->symbol }}</p>
                <form method="POST" action="{{ route('app.investments.subscribe', $product) }}" class="mt-3 space-y-2">
                    @csrf
                    <input type="number" step="0.00000001" name="amount" min="{{ $product->min_amount }}" class="input-field text-sm" placeholder="Amount ({{ $product->asset->symbol }})" required>
                    <button class="btn-brand w-full text-sm">Subscribe</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">My Subscriptions</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Product</th><th>Amount</th><th>Rewards Earned</th><th>Unlock</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($subscriptions as $s)
                    <tr>
                        <td>{{ $s->product->name }}</td>
                        <td class="font-numeric">{{ number_format($s->amount, 6) }} {{ $s->product->asset->symbol }}</td>
                        <td class="font-numeric price-up">+{{ number_format($s->rewards->sum('amount'), 8) }}</td>
                        <td class="text-text-muted">{{ $s->unlock_date?->format('M d, Y') ?? 'Flexible' }}</td>
                        <td><span class="pill-{{ $s->status === 'active' ? 'success' : 'muted' }}">{{ $s->status }}</span></td>
                        <td>
                            @if ($s->status === 'active' && (! $s->unlock_date || $s->unlock_date->isPast()))
                                <form method="POST" action="{{ route('app.investments.redeem', $s) }}">@csrf<button class="text-xs text-brand hover:underline">Redeem</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No subscriptions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
