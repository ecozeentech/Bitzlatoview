@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ editing: null }">
    <h1 class="text-2xl font-bold">Locked Balances</h1>
    <div class="risk-banner">Locked funds are normally reserved automatically by open orders, pending withdrawals, or active positions. Use these controls only to correct a genuinely stuck/erroneous state — every action here is fully audited and requires a written reason.</div>

    <div class="glass-card p-4">
        <form method="GET" class="grid gap-3 sm:grid-cols-4">
            <input type="text" name="user" value="{{ request('user') }}" class="input-field" placeholder="Search by user email/name">
            <select name="wallet_type" class="input-field">
                <option value="">All wallet types</option>
                @foreach (\App\Models\WalletAccount::TYPES as $t)
                    <option value="{{ $t }}" @selected(request('wallet_type') === $t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <select name="asset_id" class="input-field">
                <option value="">All assets</option>
                @foreach ($assets as $asset)
                    <option value="{{ $asset->id }}" @selected((string) request('asset_id') === (string) $asset->id)>{{ $asset->symbol }}</option>
                @endforeach
            </select>
            <button class="btn-outline text-sm">Filter</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Wallet</th><th>Asset</th><th>Available</th><th>Locked</th><th></th></tr></thead>
            <tbody>
                @forelse ($balances as $balance)
                    <tr>
                        <td>{{ $balance->walletAccount->user->email ?? '#'.$balance->walletAccount->user_id }}</td>
                        <td>{{ ucfirst($balance->walletAccount->type) }}</td>
                        <td>{{ $balance->asset->symbol }}</td>
                        <td class="font-numeric text-text-muted">{{ number_format($balance->available, 8) }}</td>
                        <td class="font-numeric font-semibold">{{ number_format($balance->locked, 8) }}</td>
                        <td class="space-x-2 whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.wallets.locked-balances.unlock', $balance) }}" class="inline" onsubmit="return confirm('Unlock the full {{ number_format($balance->locked, 8) }} {{ $balance->asset->symbol }} back to available for this user?')">
                                @csrf
                                <input type="hidden" name="reason" value="Manual unlock via admin panel">
                                @if (auth()->user()->two_factor_enabled)
                                    <input type="text" name="totp_code" placeholder="2FA code" class="input-field mb-1 w-24 text-xs" required>
                                @endif
                                <button class="text-xs text-brand hover:underline">Unlock All</button>
                            </form>
                            <button type="button" @click="editing = editing === {{ $balance->id }} ? null : {{ $balance->id }}" class="text-xs text-text-muted hover:underline">Edit</button>
                        </td>
                    </tr>
                    <tr x-show="editing === {{ $balance->id }}" x-cloak>
                        <td colspan="6" class="bg-surface-2">
                            <form method="POST" action="{{ route('admin.wallets.locked-balances.update', $balance) }}" class="grid gap-2 p-3 sm:grid-cols-4">
                                @csrf
                                <input type="number" step="0.00000001" min="0" name="new_amount" value="{{ $balance->locked }}" class="input-field" placeholder="New locked amount" required>
                                <input type="text" name="reason" class="input-field sm:col-span-2" placeholder="Reason for this correction (required)" required>
                                @if (auth()->user()->two_factor_enabled)
                                    <input type="text" name="totp_code" placeholder="2FA code" class="input-field" required>
                                @endif
                                <button class="btn-brand text-xs sm:col-span-1">Save New Locked Amount</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No locked balances right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $balances->links() }}
</div>
@endsection
