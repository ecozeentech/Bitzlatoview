@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Copy Trading — Traders</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Create Demo Trader</h2>
        <form method="POST" action="{{ route('admin.copy-trading.store') }}" class="grid gap-3 sm:grid-cols-4">
            @csrf
            <input type="text" name="display_name" class="input-field" placeholder="Display name" required>
            <select name="category" class="input-field"><option value="crypto">Crypto</option><option value="forex">Forex</option><option value="futures">Futures</option><option value="stock">Stock</option><option value="p2p">P2P</option></select>
            <input type="number" name="risk_score" class="input-field" placeholder="Risk score 1-100" required>
            <input type="number" step="0.1" name="return_30d_pct" class="input-field" placeholder="30d return %" required>
            <input type="number" step="0.1" name="return_90d_pct" class="input-field" placeholder="90d return %" required>
            <input type="number" step="0.1" name="max_drawdown_pct" class="input-field" placeholder="Max drawdown %" required>
            <input type="number" step="0.1" name="win_rate_pct" class="input-field" placeholder="Win rate %" required>
            <input type="text" name="strategy" class="input-field" placeholder="Strategy description">
            <button class="btn-brand sm:col-span-4">Create Trader</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Category</th><th>Followers</th><th>Allocations</th><th>Verified</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($traders as $trader)
                    <tr>
                        <td>{{ $trader->display_name }}</td>
                        <td>{{ ucfirst($trader->category) }}</td>
                        <td class="font-numeric">{{ $trader->followers_count }}</td>
                        <td class="font-numeric">{{ $trader->allocations_count }}</td>
                        <td>{{ $trader->is_verified ? 'Yes' : 'No' }}</td>
                        <td><span class="pill-{{ $trader->status === 'active' ? 'success' : 'muted' }}">{{ $trader->status }}</span></td>
                        <td class="space-x-2">
                            <form method="POST" action="{{ route('admin.copy-trading.update', $trader) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="is_verified" value="{{ $trader->is_verified ? 0 : 1 }}">
                                <input type="hidden" name="is_featured" value="{{ $trader->is_featured ? 1 : 0 }}">
                                <input type="hidden" name="status" value="{{ $trader->status }}">
                                <button class="text-xs text-brand hover:underline">Toggle Verified</button>
                            </form>
                            <form method="POST" action="{{ route('admin.copy-trading.update', $trader) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="is_verified" value="{{ $trader->is_verified ? 1 : 0 }}">
                                <input type="hidden" name="is_featured" value="{{ $trader->is_featured ? 1 : 0 }}">
                                <input type="hidden" name="status" value="{{ $trader->status === 'active' ? 'suspended' : 'active' }}">
                                <button class="text-xs text-danger hover:underline">{{ $trader->status === 'active' ? 'Suspend' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
