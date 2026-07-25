@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
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
        <h2 class="mb-3 font-semibold">Wallet Balances (read-only)</h2>
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

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Recent Ledger Entries</h2>
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

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Admin Notes</h2>
        <form method="POST" action="{{ route('admin.users.notes.store', $user) }}" class="mb-3 flex gap-2">
            @csrf
            <input type="text" name="note" class="input-field flex-1" placeholder="Add an internal note..." required>
            <button class="btn-outline text-sm">Add</button>
        </form>
        @forelse ($notes as $note)
            <div class="border-b border-border/60 py-2 text-sm"><span class="text-text-muted">{{ $note->created_at->format('M d, H:i') }}:</span> {{ $note->note }}</div>
        @empty
            <p class="text-sm text-text-muted">No notes yet.</p>
        @endforelse
    </div>
</div>
@endsection
