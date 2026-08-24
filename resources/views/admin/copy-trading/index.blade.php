@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ edit: null, tradeEdit: null, pnlEdit: null }">
    <h1 class="text-2xl font-bold">Copy Trading — Traders</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Create Trader Profile</h2>
        <form method="POST" action="{{ route('admin.copy-trading.store') }}" class="grid gap-3 sm:grid-cols-4">
            @csrf
            @include('admin.copy-trading._fields')
            <button class="btn-brand sm:col-span-4">Create Trader</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Category</th><th>Followers</th><th>Min. Copy</th><th>Return Period</th><th>Allocations</th><th>Verified</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($traders as $trader)
                    <tr>
                        <td>{{ $trader->display_name }}</td>
                        <td>{{ ucfirst($trader->category) }}</td>
                        <td class="font-numeric">{{ $trader->followers_count }}</td>
                        <td class="font-numeric">${{ number_format($trader->min_copy_amount, 2) }}</td>
                        <td>{{ $trader->lock_days }}d</td>
                        <td class="font-numeric">{{ $trader->allocations_count }}</td>
                        <td>{{ $trader->is_verified ? 'Yes' : 'No' }}</td>
                        <td><span class="pill-{{ $trader->status === 'active' ? 'success' : ($trader->status === 'sold_out' ? 'warning' : 'muted') }}">{{ str_replace('_', ' ', $trader->status) }}</span></td>
                        <td class="space-x-2 whitespace-nowrap">
                            <button type="button" @click="edit = edit === {{ $trader->id }} ? null : {{ $trader->id }}" class="text-xs text-brand hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.copy-trading.toggle-availability', $trader) }}" class="inline">
                                @csrf
                                <button class="text-xs text-brand hover:underline">{{ $trader->status === 'sold_out' ? 'Mark Available' : 'Mark Sold Out' }}</button>
                            </form>
                        </td>
                    </tr>
                    <tr x-show="edit === {{ $trader->id }}" x-cloak>
                        <td colspan="9" class="bg-surface-2/40 p-4">
                            <form method="POST" action="{{ route('admin.copy-trading.update', $trader) }}" class="grid gap-3 sm:grid-cols-4">
                                @csrf @method('PATCH')
                                @include('admin.copy-trading._fields', ['trader' => $trader])
                                <button class="btn-brand sm:col-span-4">Save Changes</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="glass-card overflow-x-auto">
        <h2 class="p-5 pb-0 font-semibold">Allocations &amp; Copy Trades</h2>
        <div class="overflow-x-auto p-5 pt-3">
            <table class="data-table">
                <thead><tr><th>User</th><th>Trader</th><th>Amount</th><th>P&amp;L</th><th>Status</th><th>Trade(s)</th><th></th></tr></thead>
                <tbody>
                    @forelse ($allocations as $a)
                        <tr>
                            <td>{{ $a->user->email }}</td>
                            <td>{{ $a->trader->display_name }}</td>
                            <td class="font-numeric">${{ number_format($a->amount, 2) }}</td>
                            <td class="font-numeric {{ $a->pnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($a->pnl, 2) }}</td>
                            <td><span class="pill-{{ $a->status === 'active' ? 'success' : ($a->status === 'stopped' ? 'muted' : 'warning') }}">{{ $a->status }}</span></td>
                            <td>
                                @foreach ($a->trades as $trade)
                                    <button type="button" @click="tradeEdit = tradeEdit === {{ $trade->id }} ? null : {{ $trade->id }}" class="mr-1 text-xs {{ $trade->closed_at ? 'text-text-muted' : 'text-brand hover:underline' }}">
                                        {{ $trade->asset_symbol }} @{{ number_format($trade->entry_price, 2) }}
                                    </button>
                                @endforeach
                            </td>
                            <td>
                                @if ($a->status === 'stopped')
                                    <button type="button" @click="pnlEdit = pnlEdit === {{ $a->id }} ? null : {{ $a->id }}" class="text-xs text-brand hover:underline">Adjust P&amp;L</button>
                                @endif
                            </td>
                        </tr>
                        @if ($a->status === 'stopped')
                            <tr x-show="pnlEdit === {{ $a->id }}" x-cloak>
                                <td colspan="7" class="bg-surface-2/40 p-3">
                                    <form method="POST" action="{{ route('admin.copy-trading.allocations.adjust-pnl', $a) }}" class="flex flex-wrap items-end gap-2">
                                        @csrf
                                        <div><label class="label-field">New P&amp;L</label><input type="number" step="0.01" name="new_pnl" value="{{ $a->pnl }}" class="input-field w-32" required></div>
                                        <div class="flex-1"><label class="label-field">Reason (audited)</label><input type="text" name="reason" class="input-field" required></div>
                                        <button class="btn-outline text-xs">Post Correction</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                        @foreach ($a->trades as $trade)
                            <tr x-show="tradeEdit === {{ $trade->id }}" x-cloak>
                                <td colspan="7" class="bg-surface-2/40 p-3">
                                    @if ($trade->closed_at)
                                        <p class="text-xs text-text-muted">This trade is already settled and can no longer be edited directly — use "Adjust P&amp;L" above instead.</p>
                                    @else
                                        <form method="POST" action="{{ route('admin.copy-trading.trades.update', $trade) }}" class="flex flex-wrap items-end gap-2">
                                            @csrf @method('PATCH')
                                            <div><label class="label-field">Asset symbol</label><input type="text" name="asset_symbol" value="{{ $trade->asset_symbol }}" class="input-field w-32" required></div>
                                            <div>
                                                <label class="label-field">Side</label>
                                                <select name="side" class="input-field w-28">
                                                    <option value="long" @selected($trade->side === 'long')>Long</option>
                                                    <option value="short" @selected($trade->side === 'short')>Short</option>
                                                </select>
                                            </div>
                                            <div><label class="label-field">Entry price</label><input type="number" step="0.00000001" name="entry_price" value="{{ $trade->entry_price }}" class="input-field w-40" required></div>
                                            <button class="btn-outline text-xs">Save</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="7" class="text-center text-text-muted">No allocations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
