@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold">{{ $wallet->label() }}</h1>
                @if ($wallet->is_suspended)
                    <span class="pill-danger">Suspended</span>
                @endif
            </div>
            <p class="font-numeric text-lg text-text-muted">≈ ${{ number_format($total, 2) }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ url('/app/funding/deposit') }}?wallet={{ $type }}" class="btn-brand text-sm">Deposit</a>
            @unless ($wallet->is_suspended)
            <a href="{{ url('/app/funding/withdraw') }}?wallet={{ $type }}" class="btn-outline text-sm">Withdraw</a>
            <details class="relative inline-block">
                <summary class="btn-ghost cursor-pointer text-sm">Transfer ▾</summary>
                <div class="absolute right-0 z-10 mt-2 w-80 glass-card p-4">
                    <form method="POST" action="{{ route('app.wallet.transfer') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="from_type" value="{{ $type }}">
                        <div>
                            <label class="label-field">To wallet</label>
                            <select name="to_type" class="input-field">
                                @foreach ($otherWallets as $t)
                                    <option value="{{ $t }}">{{ ucfirst($t) }} Wallet</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label-field">Asset</label>
                            <select name="asset_id" class="input-field">
                                @foreach ($rows as $row)
                                    <option value="{{ $row['asset']->id }}">{{ $row['asset']->symbol }} (avail {{ number_format($row['available'], 8) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label-field">Amount</label>
                            <input type="number" step="0.00000001" name="amount" class="input-field" required>
                        </div>
                        <div>
                            <label class="label-field">Note (optional)</label>
                            <input type="text" name="note" class="input-field" placeholder="Funding note">
                        </div>
                        <button class="btn-brand w-full text-sm">Confirm Transfer</button>
                    </form>
                </div>
            </details>
            @endunless
        </div>
    </div>

    @if ($wallet->is_suspended)
        <div class="risk-banner">This wallet is suspended{{ $wallet->suspension_reason ? ': '.$wallet->suspension_reason : '.' }} Withdrawals and internal transfers out of it are blocked. Contact support if you believe this is a mistake.</div>
    @endif

    @if ($pendingWithdrawals->isNotEmpty())
        <div class="glass-card p-5">
            <h2 class="mb-1 font-semibold">Pending Withdrawals from this Wallet</h2>
            <p class="mb-3 text-xs text-text-muted">Already deducted from Available and held under Locked above while awaiting admin review.</p>
            <table class="data-table">
                <thead><tr><th>Date</th><th>Asset</th><th>Amount</th><th>Fee</th><th>Net</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($pendingWithdrawals as $w)
                        <tr>
                            <td class="text-text-muted">{{ $w->created_at->format('M d, H:i') }}</td>
                            <td>{{ $w->asset->symbol }}</td>
                            <td class="font-numeric">{{ number_format($w->amount, 8) }}</td>
                            <td class="font-numeric text-text-muted">{{ number_format($w->fee, 8) }}</td>
                            <td class="font-numeric">{{ number_format($w->net_amount ?: ($w->amount - $w->fee), 8) }}</td>
                            <td><span class="pill-warning">{{ str_replace('_', ' ', ucfirst($w->status)) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Asset</th><th>Available</th><th>Locked</th><th>Total</th><th>≈ USD</th></tr></thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td class="flex items-center gap-2"><x-asset-icon :symbol="$row['asset']->symbol" /> {{ $row['asset']->symbol }} <span class="text-xs text-text-muted">{{ $row['asset']->name }}</span></td>
                        <td class="font-numeric">{{ number_format($row['available'], 8) }}</td>
                        <td class="font-numeric text-text-muted">{{ number_format($row['locked'], 8) }}</td>
                        <td class="font-numeric">{{ number_format($row['available'] + $row['locked'], 8) }}</td>
                        <td class="font-numeric">${{ number_format($row['usd'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Recent Wallet Activity</h2>
        <table class="data-table">
            <thead><tr><th>Date</th><th>Direction</th><th>Asset</th><th>Amount</th><th>Note</th></tr></thead>
            <tbody>
                @forelse ($history as $t)
                    <tr>
                        <td class="text-text-muted">{{ $t->created_at->format('M d, H:i') }}</td>
                        <td>{{ $t->from_wallet_account_id === $wallet->id ? 'Out → '.ucfirst($t->toWallet->type) : 'In ← '.ucfirst($t->fromWallet->type) }}</td>
                        <td>{{ $t->asset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($t->amount, 8) }}</td>
                        <td class="text-text-muted">{{ $t->user_note }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No transfers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
