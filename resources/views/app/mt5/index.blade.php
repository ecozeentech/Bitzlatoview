@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">MetaTrader 5 / Meta Trading</h1>
    <div class="risk-banner"><strong class="text-danger">Not connected to a real broker yet.</strong> MT5 officially supports forex, stocks, futures, algorithmic and copy trading, but a live connection requires a licensed broker's Manager API and proper regulatory approval. Accounts saved here do not sync with any real MT5 server — positions shown are placeholder data only. Credentials are encrypted at rest and never displayed in plain text.</div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="glass-card p-4 text-center"><p class="text-xs text-text-muted">Web Terminal</p><p class="mt-2 text-sm">Coming soon — requires broker/provider licensing</p></div>
        <div class="glass-card p-4 text-center"><p class="text-xs text-text-muted">EA Marketplace</p><p class="mt-2 text-sm">Coming soon — MQL5/Expert Advisor listings</p></div>
        <div class="glass-card p-4 text-center"><p class="text-xs text-text-muted">VPS</p><p class="mt-2 text-sm">Coming soon — low-latency VPS for EAs</p></div>
    </div>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Connect a Broker / MT5 Account</h2>
        <form method="POST" action="{{ route('app.mt5.connect') }}" class="grid gap-3 sm:grid-cols-3">
            @csrf
            <input type="text" name="broker_name" class="input-field" placeholder="Broker name" required>
            <input type="text" name="mt5_login" class="input-field" placeholder="MT5 login" required>
            <input type="text" name="server_name" class="input-field" placeholder="Server name" required>
            <select name="account_type" class="input-field"><option value="demo">Demo</option><option value="standard">Standard</option><option value="ecn">ECN</option></select>
            <select name="leverage" class="input-field"><option value="50">1:50</option><option value="100" selected>1:100</option><option value="200">1:200</option><option value="500">1:500</option></select>
            <input type="text" name="currency" class="input-field" value="USD" placeholder="Currency" required>
            <input type="password" name="password" class="input-field sm:col-span-3" placeholder="MT5 password (encrypted, never stored in plain text)" required>
            <button class="btn-brand sm:col-span-3">Connect Account</button>
        </form>
    </div>

    @foreach ($accounts as $account)
        <div class="glass-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold">{{ $account->broker_name }} — #{{ $account->mt5_login }}</p>
                    <p class="text-xs text-text-muted">{{ $account->server_name }} · {{ ucfirst($account->account_type) }} · 1:{{ $account->leverage }} · {{ $account->currency }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="pill-{{ $account->status === 'connected' ? 'success' : 'muted' }}">{{ $account->status }}</span>
                    @if ($account->status === 'connected')
                        <form method="POST" action="{{ route('app.mt5.sync', $account) }}">@csrf<button class="btn-outline text-xs">Sync</button></form>
                        <form method="POST" action="{{ route('app.mt5.disconnect', $account) }}">@csrf<button class="text-xs text-danger hover:underline">Disconnect</button></form>
                    @endif
                </div>
            </div>
            <p class="mt-2 text-xs text-text-muted">Last sync: {{ $account->last_sync_at?->diffForHumans() ?? 'Never' }}</p>

            <table class="data-table mt-3">
                <thead><tr><th>Symbol</th><th>Side</th><th>Volume</th><th>Open</th><th>Current</th><th>P&amp;L</th></tr></thead>
                <tbody>
                    @forelse ($account->positions as $pos)
                        <tr>
                            <td>{{ $pos->symbol }}</td>
                            <td class="{{ $pos->side === 'buy' ? 'price-up' : 'price-down' }}">{{ ucfirst($pos->side) }}</td>
                            <td class="font-numeric">{{ $pos->volume }}</td>
                            <td class="font-numeric">{{ number_format($pos->open_price, 3) }}</td>
                            <td class="font-numeric">{{ number_format($pos->current_price, 3) }}</td>
                            <td class="font-numeric {{ $pos->pnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($pos->pnl, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-text-muted">No open positions synced.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
</div>
@endsection
