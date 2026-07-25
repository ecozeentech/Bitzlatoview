@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">MetaTrader Connected Accounts</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Broker</th><th>Login</th><th>Server</th><th>Status</th><th>Positions</th></tr></thead>
            <tbody>
                @forelse ($accounts as $a)
                    <tr>
                        <td>{{ $a->user->email }}</td>
                        <td>{{ $a->broker_name }}</td>
                        <td class="font-numeric">{{ $a->mt5_login }}</td>
                        <td class="text-text-muted">{{ $a->server_name }}</td>
                        <td><span class="pill-{{ $a->status === 'connected' ? 'success' : 'muted' }}">{{ $a->status }}</span></td>
                        <td class="font-numeric">{{ $a->positions->count() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No connected accounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
