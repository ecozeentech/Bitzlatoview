@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Admin Dashboard</h1>

    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Total Users</p><p class="font-numeric text-xl font-bold">{{ number_format($stats['total_users']) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">New (7d)</p><p class="font-numeric text-xl font-bold">{{ number_format($stats['new_users_7d']) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">KYC Pending</p><p class="font-numeric text-xl font-bold">{{ number_format($stats['kyc_pending']) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Pending Withdrawals</p><p class="font-numeric text-xl font-bold">{{ number_format($stats['pending_withdrawals']) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">P2P Disputes</p><p class="font-numeric text-xl font-bold">{{ number_format($stats['p2p_disputes']) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Support Open</p><p class="font-numeric text-xl font-bold">{{ number_format($stats['support_open']) }}</p></div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Deposit Volume</p><p class="font-numeric text-xl font-bold price-up">${{ number_format($stats['deposit_volume'], 0) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Withdrawal Volume</p><p class="font-numeric text-xl font-bold price-down">${{ number_format($stats['withdrawal_volume'], 0) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Trading Volume</p><p class="font-numeric text-xl font-bold">${{ number_format($stats['trading_volume'], 0) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Fee Revenue</p><p class="font-numeric text-xl font-bold text-brand">${{ number_format($stats['revenue_fees'], 2) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Active Bots</p><p class="font-numeric text-xl font-bold">{{ number_format($stats['active_bots']) }}</p></div>
        <div class="glass-card p-4"><p class="text-xs text-text-muted">Active Mining</p><p class="font-numeric text-xl font-bold">{{ number_format($stats['active_mining']) }}</p></div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="glass-card p-5">
            <h2 class="mb-3 font-semibold">Recent Users</h2>
            <table class="data-table">
                <thead><tr><th>Name</th><th>Country</th><th>KYC</th><th>Joined</th></tr></thead>
                <tbody>
                    @foreach ($recentUsers as $u)
                        <tr>
                            <td><a href="{{ route('admin.users.show', $u) }}" class="text-brand hover:underline">{{ $u->name }}</a></td>
                            <td class="text-text-muted">{{ $u->country }}</td>
                            <td><span class="pill-{{ $u->kyc_status === 'approved' ? 'success' : 'warning' }}">{{ str_replace('_',' ',$u->kyc_status) }}</span></td>
                            <td class="text-text-muted">{{ $u->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="glass-card p-5">
            <h2 class="mb-3 font-semibold">Recent Withdrawals</h2>
            <table class="data-table">
                <thead><tr><th>User</th><th>Asset</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($recentWithdrawals as $w)
                        <tr>
                            <td>{{ $w->user->name }}</td>
                            <td>{{ $w->asset->symbol }}</td>
                            <td class="font-numeric">{{ number_format($w->amount, 4) }}</td>
                            <td><span class="pill-{{ $w->status === 'completed' ? 'success' : 'warning' }}">{{ str_replace('_',' ',$w->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
