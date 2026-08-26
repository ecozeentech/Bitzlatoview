@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
            <p class="text-sm text-text-muted">{{ $user->email }} · {{ $user->country }} · Joined {{ $user->created_at->format('M d, Y') }}</p>
        </div>
        <div class="flex gap-2">
            @if ($user->status === 'active')
                <form method="POST" action="{{ route('admin.users.suspend', $user) }}">@csrf<button class="btn-outline text-sm">Suspend</button></form>
            @else
                <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}">@csrf<button class="btn-brand text-sm">Reactivate</button></form>
            @endif
            <form method="POST" action="{{ route('admin.users.force-password-reset', $user) }}">@csrf<button class="btn-outline text-sm">Force Password Reset</button></form>
            <form method="POST" action="{{ route('admin.users.login-as', $user) }}" onsubmit="return confirm('Log in as {{ $user->name }}? This is fully audited and will switch your active session to their account.')">
                @csrf
                <button class="btn-brand text-sm">Login as User</button>
            </form>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="glass-card p-4"><p class="text-xs text-text-muted">KYC Status</p><p class="pill-{{ $user->kyc_status === 'approved' ? 'success' : 'warning' }} mt-1">{{ str_replace('_',' ',$user->kyc_status) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Account Status</p><p class="pill-{{ $user->status === 'active' ? 'success' : 'danger' }} mt-1">{{ $user->status }}</p></div>
        <div class="glass-card p-4">
            <p class="text-xs text-text-muted">Role</p>
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-1 flex gap-2">
                @csrf @method('PATCH')
                <select name="role" class="input-field text-sm">
                    @foreach (['user','support','compliance','admin'] as $r)
                        <option value="{{ $r }}" @selected($user->role === $r)>{{ $r }}</option>
                    @endforeach
                </select>
                <button class="btn-outline text-xs">Save</button>
            </form>
        </div>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Add / Remove Funds</h2>
        <p class="mb-3 text-xs text-text-muted">Applies immediately — posts a real, audited ledger entry with no second-admin approval step. See <a href="{{ route('admin.adjustments.index') }}" class="text-brand hover:underline">Balance Adjustments</a> for the full history.</p>
        <form method="POST" action="{{ route('admin.adjustments.store') }}" class="grid gap-3 sm:grid-cols-5">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <select name="wallet_type" class="input-field">
                <option value="primary">Primary</option>
                <option value="trading">Trading</option>
                <option value="investment">Investment</option>
            </select>
            <select name="asset_id" class="input-field">
                @foreach ($assets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->symbol }}</option>
                @endforeach
            </select>
            <select name="direction" class="input-field">
                <option value="credit">Add funds (credit)</option>
                <option value="debit">Remove funds (debit)</option>
            </select>
            <input type="number" step="0.00000001" name="amount" class="input-field" placeholder="Amount" required>
            <button class="btn-brand text-sm">Request</button>
            <input type="text" name="reason" class="input-field sm:col-span-5" placeholder="Reason (required, audited)" required>
        </form>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Wallet Access</h2>
        <p class="mb-3 text-xs text-text-muted">Suspending a wallet blocks internal transfers out of it and withdrawal requests from it. Deposits and balance viewing are unaffected.</p>
        <div class="grid gap-3 sm:grid-cols-3">
            @foreach (\App\Models\WalletAccount::TYPES as $type)
                @php $wallet = $user->walletAccounts->firstWhere('type', $type); @endphp
                <div class="rounded-lg border border-border p-3">
                    <div class="flex items-center justify-between">
                        <p class="font-medium">{{ ucfirst($type) }} Wallet</p>
                        <span class="pill-{{ $wallet?->is_suspended ? 'danger' : 'success' }}">{{ $wallet?->is_suspended ? 'Suspended' : 'Active' }}</span>
                    </div>
                    @if ($wallet?->is_suspended && $wallet->suspension_reason)
                        <p class="mt-1 text-xs text-text-muted">Reason: {{ $wallet->suspension_reason }}</p>
                    @endif
                    <form method="POST" action="{{ route('admin.users.wallets.toggle-suspension', [$user, $type]) }}" class="mt-2">
                        @csrf
                        @unless ($wallet?->is_suspended)
                            <input type="text" name="reason" class="input-field mb-2 text-xs" placeholder="Reason (optional)">
                        @endunless
                        <button class="btn-outline w-full text-xs">{{ $wallet?->is_suspended ? 'Reactivate Wallet' : 'Suspend Wallet' }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Wallet Balances (read-only)</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Wallet</th><th>Asset</th><th>Available</th><th>Locked</th></tr></thead>
            <tbody>
                @foreach ($user->walletAccounts as $wallet)
                    @foreach ($wallet->balances as $balance)
                        @if ($balance->available > 0 || $balance->locked > 0)
                            <tr>
                                <td>{{ ucfirst($wallet->type) }}</td>
                                <td>{{ $balance->asset->symbol }}</td>
                                <td class="font-numeric">{{ number_format($balance->available, 8) }}</td>
                                <td class="font-numeric text-text-muted">{{ number_format($balance->locked, 8) }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Recent Ledger Entries</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Date</th><th>Asset</th><th>Direction</th><th>Amount</th><th>Balance After</th></tr></thead>
            <tbody>
                @forelse ($ledgerEntries as $e)
                    <tr>
                        <td class="text-text-muted">{{ $e->created_at->format('M d, H:i') }}</td>
                        <td>{{ $e->asset->symbol }}</td>
                        <td class="{{ $e->direction === 'credit' ? 'price-up' : 'price-down' }}">{{ ucfirst($e->direction) }}</td>
                        <td class="font-numeric">{{ number_format($e->amount, 8) }}</td>
                        <td class="font-numeric text-text-muted">{{ number_format($e->balance_after, 8) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No ledger activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="glass-card p-5" x-data="{ editNote: null }">
        <h2 class="mb-3 font-semibold">Admin Notes</h2>
        <p class="mb-3 text-xs text-text-muted">Internal only — never shown to the user.</p>
        <form method="POST" action="{{ route('admin.users.notes.store', $user) }}" class="mb-3 flex gap-2">
            @csrf
            <input type="text" name="note" class="input-field flex-1" placeholder="Add an internal note..." required>
            <button class="btn-outline text-sm">Add</button>
        </form>
        @forelse ($notes as $note)
            <div class="border-b border-border/60 py-2 text-sm">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="text-text-muted">{{ $note->created_at->format('M d, H:i') }}:</span> {{ $note->note }}
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button type="button" @click="editNote = editNote === {{ $note->id }} ? null : {{ $note->id }}" class="text-xs text-brand hover:underline">Edit</button>
                        <form method="POST" action="{{ route('admin.users.notes.destroy', $note) }}" onsubmit="return confirm('Delete this note?')">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                    </div>
                </div>
                <form x-show="editNote === {{ $note->id }}" x-cloak method="POST" action="{{ route('admin.users.notes.update', $note) }}" class="mt-2 flex gap-2">
                    @csrf @method('PATCH')
                    <input type="text" name="note" class="input-field flex-1 text-sm" value="{{ $note->note }}" required>
                    <button class="btn-brand text-xs">Save</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-text-muted">No notes yet.</p>
        @endforelse
    </div>
</div>
@endsection
