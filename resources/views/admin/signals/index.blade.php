@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ showCreate: false, edit: null, adjust: null }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Signals</h1>
        <button type="button" @click="showCreate = !showCreate" class="btn-brand text-sm">+ New Package</button>
    </div>

    <p class="text-xs text-text-muted">Expected returns are disclosed estimates, not guarantees. Signals settle against the tracked asset's real market price movement — set "Tracked asset" to a real listed symbol (e.g. BTC, ETH).</p>

    <div x-show="showCreate" x-cloak class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Create signal package</h2>
        <form method="POST" action="{{ route('admin.signals.store') }}" class="grid gap-3 md:grid-cols-3">
            @csrf
            @include('admin.signals._fields')
            <div class="md:col-span-3">
                <button class="btn-brand text-sm">Create Package</button>
            </div>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Tracks</th><th>Expected Return</th><th>Risk</th><th>Duration</th><th>Min / Max</th><th>Fee</th><th>Subs</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($packages as $p)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $p->name }}</p>
                            <p class="text-xs text-text-muted">{{ \Illuminate\Support\Str::limit($p->description, 50) }}</p>
                        </td>
                        <td>{{ $p->tracked_asset_symbol }}</td>
                        <td class="font-numeric price-up">{{ $p->expected_return_pct }}%</td>
                        <td><span class="pill-{{ $p->risk_level === 'high' ? 'danger' : ($p->risk_level === 'low' ? 'success' : 'info') }}">{{ ucfirst($p->risk_level) }}</span></td>
                        <td>{{ $p->duration_days }}d</td>
                        <td class="font-numeric text-text-muted">${{ number_format($p->min_investment) }}{{ $p->max_investment ? ' – $'.number_format($p->max_investment) : '+' }}</td>
                        <td>{{ $p->fee_pct }}%</td>
                        <td class="font-numeric">{{ $p->subscriptions_count }}</td>
                        <td><span class="pill-{{ $p->status === 'active' ? 'success' : 'muted' }}">{{ $p->status }}</span></td>
                        <td class="flex gap-2">
                            <button type="button" @click="edit = edit === {{ $p->id }} ? null : {{ $p->id }}" class="text-xs text-brand hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.signals.toggle', $p) }}">@csrf<button class="text-xs text-text-muted hover:underline">{{ $p->status === 'active' ? 'Pause' : 'Activate' }}</button></form>
                            @if ($p->subscriptions_count === 0)
                                <form method="POST" action="{{ route('admin.signals.destroy', $p) }}" onsubmit="return confirm('Delete this package?')">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                            @endif
                        </td>
                    </tr>
                    <tr x-show="edit === {{ $p->id }}" x-cloak>
                        <td colspan="10" class="bg-surface-2/40 p-4">
                            <form method="POST" action="{{ route('admin.signals.update', $p) }}" class="grid gap-3 md:grid-cols-3">
                                @csrf
                                @method('PUT')
                                @include('admin.signals._fields', ['package' => $p])
                                <div class="md:col-span-3">
                                    <button class="btn-brand text-sm">Save Changes</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-text-muted">No signal packages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="glass-card overflow-x-auto">
        <h2 class="p-5 pb-0 font-semibold">Recent Subscriptions</h2>
        <div class="overflow-x-auto p-5 pt-3">
            <table class="data-table">
                <thead><tr><th>User</th><th>Package</th><th>Amount</th><th>P&amp;L</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($subscriptions as $s)
                        <tr>
                            <td>{{ $s->user->email }}</td>
                            <td>{{ $s->package->name }}</td>
                            <td class="font-numeric">${{ number_format($s->amount, 2) }}</td>
                            <td class="font-numeric {{ $s->pnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($s->pnl, 2) }}</td>
                            <td><span class="pill-{{ $s->status === 'active' ? 'success' : ($s->status === 'stopped' ? 'muted' : 'warning') }}">{{ $s->status }}</span></td>
                            <td>
                                @if ($s->status === 'stopped')
                                    <button type="button" @click="adjust = adjust === {{ $s->id }} ? null : {{ $s->id }}" class="text-xs text-brand hover:underline">Adjust P&amp;L</button>
                                @endif
                            </td>
                        </tr>
                        @if ($s->status === 'stopped')
                            <tr x-show="adjust === {{ $s->id }}" x-cloak>
                                <td colspan="6" class="bg-surface-2/40 p-3">
                                    <form method="POST" action="{{ route('admin.signals.subscriptions.adjust', $s) }}" class="flex flex-wrap items-end gap-2">
                                        @csrf
                                        <div>
                                            <label class="label-field">New P&amp;L</label>
                                            <input type="number" step="0.01" name="new_pnl" value="{{ $s->pnl }}" class="input-field w-32" required>
                                        </div>
                                        <div class="flex-1">
                                            <label class="label-field">Reason (audited)</label>
                                            <input type="text" name="reason" class="input-field" placeholder="Why is this being corrected?" required>
                                        </div>
                                        <button class="btn-outline text-xs">Post Correction</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="text-center text-text-muted">No subscriptions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
