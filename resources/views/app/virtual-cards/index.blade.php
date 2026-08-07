@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Virtual Cards</h1>
    <div class="risk-banner"><strong class="text-danger">Not a real, spendable payment card yet.</strong> No licensed card-issuing provider is connected. Real cards must be issued by a regulated bank partner under a Visa/Mastercard license (e.g. via Stripe Issuing, Marqeta or Lithic) — this section will only produce usable cards once that integration is complete.</div>

    @if (auth()->user()->kyc_status !== 'approved')
        <div class="glass-card p-5 text-sm text-text-muted">Card creation requires an approved KYC status. <a href="{{ url('/app/settings/kyc') }}" class="text-brand hover:underline">Complete verification</a>.</div>
    @else
        <div class="glass-card p-6">
            <h2 class="mb-3 font-semibold">Create Virtual Card</h2>
            <form method="POST" action="{{ route('app.virtual-cards.store') }}" class="grid gap-3 sm:grid-cols-3">
                @csrf
                <input type="text" name="nickname" class="input-field" placeholder="Card nickname">
                <input type="number" step="0.01" name="spending_limit" class="input-field" placeholder="Spending limit" required>
                <select name="currency" class="input-field"><option>USD</option><option>EUR</option><option>GBP</option></select>
                <button class="btn-brand sm:col-span-3">Create Card</button>
            </form>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($cards as $card)
            <div class="glass-card p-5" x-data="{ revealed: false, number: '', cvv: '' }">
                <div class="rounded-xl bg-gradient-to-br from-brand/30 via-surface-2 to-purple/20 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-text-muted">{{ $card->nickname ?? 'Bitzlatoview Card' }}</p>
                        <span class="pill-danger">Not real / not spendable</span>
                    </div>
                    <p class="mt-4 font-numeric text-lg tracking-wider" x-text="revealed ? number : '{{ $card->masked_number }}'"></p>
                    <div class="mt-3 flex justify-between text-xs">
                        <span>{{ $card->cardholder_name }}</span>
                        <span>{{ $card->expiry_month }}/{{ $card->expiry_year }} <span x-show="revealed" x-text="cvv"></span></span>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-sm">
                    <span class="pill-{{ $card->status === 'active' ? 'success' : ($card->status === 'frozen' ? 'warning' : 'muted') }}">{{ $card->status }}</span>
                    <span class="text-text-muted">Limit: ${{ number_format($card->spending_limit, 2) }} {{ $card->currency }}</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" @click="fetch('{{ route('app.virtual-cards.reveal', $card) }}', {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}}).then(r=>r.json()).then(d=>{number=d.number; cvv=d.cvv; revealed=!revealed})" class="btn-outline text-xs" x-text="revealed ? 'Hide' : 'Reveal'"></button>
                    @if ($card->status === 'active')
                        <form method="POST" action="{{ route('app.virtual-cards.freeze', $card) }}">@csrf<button class="btn-outline text-xs">Freeze</button></form>
                    @elseif ($card->status === 'frozen')
                        <form method="POST" action="{{ route('app.virtual-cards.unfreeze', $card) }}">@csrf<button class="btn-outline text-xs">Unfreeze</button></form>
                    @endif
                    <form method="POST" action="{{ route('app.virtual-cards.fund', $card) }}" class="flex gap-1">
                        @csrf
                        <input type="number" step="0.01" name="amount" class="input-field w-24 text-xs" placeholder="Fund $">
                        <button class="btn-brand text-xs">Fund</button>
                    </form>
                    <form method="POST" action="{{ route('app.virtual-cards.cancel', $card) }}">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Cancel</button></form>
                </div>

                <table class="data-table mt-3 text-xs">
                    <thead><tr><th>Date</th><th>Merchant</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($card->transactions as $t)
                            <tr><td class="text-text-muted">{{ $t->occurred_at->format('M d') }}</td><td>{{ $t->merchant }}</td><td class="font-numeric">${{ number_format($t->amount, 2) }}</td><td>{{ $t->status }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-text-muted">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</div>
@endsection
