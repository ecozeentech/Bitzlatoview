@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ showSettings: false }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Virtual Cards</h1>
        <button type="button" @click="showSettings = !showSettings" class="btn-outline text-sm">Card Settings</button>
    </div>

    <div class="risk-banner">Virtual cards are a card-management UI pending a licensed issuing provider (Stripe Issuing / Marqeta / Lithic). No real payment network transactions occur yet. Toggle the "Virtual Cards" feature flag under System → Feature Flags to enable/disable new requests platform-wide.</div>

    <div x-show="showSettings" x-cloak class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Issuing limits &amp; fees</h2>
        <form method="POST" action="{{ route('admin.virtual-cards.settings.update') }}" class="grid gap-3 md:grid-cols-4">
            @csrf
            <div>
                <label class="label-field">Max spending limit</label>
                <input type="number" step="0.01" name="max_spending_limit" class="input-field" value="{{ $settings->max_spending_limit }}" required>
            </div>
            <div>
                <label class="label-field">Issuance fee</label>
                <input type="number" step="0.01" name="issuance_fee" class="input-field" value="{{ $settings->issuance_fee }}" required>
            </div>
            <div>
                <label class="label-field">Funding fee %</label>
                <input type="number" step="0.01" name="funding_fee_pct" class="input-field" value="{{ $settings->funding_fee_pct }}" required>
            </div>
            <div>
                <label class="label-field">Monthly fee</label>
                <input type="number" step="0.01" name="monthly_fee" class="input-field" value="{{ $settings->monthly_fee }}" required>
            </div>
            <div class="md:col-span-4">
                <label class="label-field">Allowed currencies</label>
                <div class="flex gap-4">
                    @foreach (['USD', 'EUR', 'GBP'] as $cur)
                        <label class="inline-flex items-center gap-1 text-sm">
                            <input type="checkbox" name="allowed_currencies[]" value="{{ $cur }}" @checked(in_array($cur, $settings->allowed_currencies)) class="rounded border-border bg-surface-2 text-brand focus:ring-brand">
                            {{ $cur }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="md:col-span-4">
                <button class="btn-brand text-sm">Save Settings</button>
            </div>
        </form>
    </div>

    @if ($pending->isNotEmpty())
        <div class="glass-card p-5">
            <h2 class="mb-3 font-semibold">Pending Requests ({{ $pending->count() }})</h2>
            <table class="data-table">
                <thead><tr><th>User</th><th>Nickname</th><th>Limit</th><th>Requested</th><th></th></tr></thead>
                <tbody>
                    @foreach ($pending as $card)
                        <tr>
                            <td>{{ $card->user->email }}</td>
                            <td>{{ $card->nickname ?? '—' }}</td>
                            <td class="font-numeric">{{ number_format($card->spending_limit, 2) }} {{ $card->currency }}</td>
                            <td class="text-text-muted">{{ $card->created_at->diffForHumans() }}</td>
                            <td class="flex gap-2">
                                <form method="POST" action="{{ route('admin.virtual-cards.approve', $card) }}">@csrf<button class="text-xs text-success hover:underline">Approve</button></form>
                                <form method="POST" action="{{ route('admin.virtual-cards.reject', $card) }}" onsubmit="return promptReject(this)">
                                    @csrf
                                    <input type="hidden" name="rejection_reason" value="">
                                    <button type="button" onclick="promptReject(this.form)" class="text-xs text-danger hover:underline">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Card</th><th>Limit</th><th>Status</th><th>Transactions</th><th></th></tr></thead>
            <tbody>
                @forelse ($cards as $card)
                    <tr>
                        <td>{{ $card->user->email }}</td>
                        <td class="font-numeric">{{ $card->masked_number }}</td>
                        <td class="font-numeric">${{ number_format($card->spending_limit, 2) }} {{ $card->currency }}</td>
                        <td><span class="pill-{{ $card->status === 'active' ? 'success' : ($card->status === 'frozen' ? 'warning' : 'muted') }}">{{ $card->status }}</span></td>
                        <td class="font-numeric">{{ $card->transactions->count() }}</td>
                        <td>
                            @if ($card->status === 'active')
                                <form method="POST" action="{{ route('admin.virtual-cards.freeze', $card) }}">@csrf<button class="text-xs text-danger hover:underline">Freeze</button></form>
                            @elseif ($card->status === 'frozen')
                                <form method="POST" action="{{ route('admin.virtual-cards.unfreeze', $card) }}">@csrf<button class="text-xs text-brand hover:underline">Unfreeze</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No virtual cards issued yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $cards->links() }}
</div>

<script>
    function promptReject(form) {
        const reason = prompt('Reason for rejecting this card request:');
        if (!reason) return false;
        form.querySelector('input[name="rejection_reason"]').value = reason;
        form.submit();
        return false;
    }
</script>
@endsection
