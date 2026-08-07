@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ showCreate: false, edit: null }">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Investment Products</h1>
        <button type="button" @click="showCreate = !showCreate" class="btn-brand text-sm">+ New Product</button>
    </div>

    <p class="text-xs text-text-muted">Expected returns are disclosed estimates, not guarantees. Actual accrual depends on platform performance and can change — reflect this in every product description.</p>

    <div x-show="showCreate" x-cloak class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Create investment product</h2>
        <form method="POST" action="{{ route('admin.investments.store') }}" class="grid gap-3 md:grid-cols-3">
            @csrf
            @include('admin.investments._fields')
            <div class="md:col-span-3">
                <button class="btn-brand text-sm">Create Product</button>
            </div>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Asset</th><th>Expected Return</th><th>Risk</th><th>Lock</th><th>Payout</th><th>Min / Max</th><th>Subscriptions</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($products as $p)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $p->name }}</p>
                            <p class="text-xs text-text-muted">{{ \Illuminate\Support\Str::limit($p->description, 50) }}</p>
                        </td>
                        <td>{{ $p->asset->symbol }}</td>
                        <td class="font-numeric price-up">{{ $p->apy_pct }}%</td>
                        <td><span class="pill-{{ $p->risk_level === 'high' ? 'danger' : ($p->risk_level === 'low' ? 'success' : 'info') }}">{{ ucfirst($p->risk_level) }}</span></td>
                        <td>{{ $p->lock_days }}d</td>
                        <td class="text-text-muted">{{ ucfirst($p->payout_frequency) }}</td>
                        <td class="font-numeric text-text-muted">${{ number_format($p->min_amount) }}{{ $p->max_amount ? ' – $'.number_format($p->max_amount) : '+' }}</td>
                        <td class="font-numeric">{{ $p->subscriptions_count }}</td>
                        <td><span class="pill-{{ $p->status === 'active' ? 'success' : 'muted' }}">{{ $p->status }}</span></td>
                        <td class="flex gap-2">
                            <button type="button" @click="edit = edit === {{ $p->id }} ? null : {{ $p->id }}" class="text-xs text-brand hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.investments.toggle', $p) }}">@csrf<button class="text-xs text-text-muted hover:underline">{{ $p->status === 'active' ? 'Pause' : 'Activate' }}</button></form>
                            @if ($p->subscriptions_count === 0)
                                <form method="POST" action="{{ route('admin.investments.destroy', $p) }}" onsubmit="return confirm('Delete this product?')">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                            @endif
                        </td>
                    </tr>
                    <tr x-show="edit === {{ $p->id }}" x-cloak>
                        <td colspan="10" class="bg-surface-2/40 p-4">
                            <form method="POST" action="{{ route('admin.investments.update', $p) }}" class="grid gap-3 md:grid-cols-3">
                                @csrf
                                @method('PUT')
                                @include('admin.investments._fields', ['product' => $p])
                                <div class="md:col-span-3">
                                    <button class="btn-brand text-sm">Save Changes</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-text-muted">No investment products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
