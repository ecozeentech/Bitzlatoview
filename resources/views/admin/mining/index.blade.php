@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Mining Packages</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Create Package</h2>
        <form method="POST" action="{{ route('admin.mining.store') }}" class="grid gap-3 sm:grid-cols-4">
            @csrf
            <input type="text" name="name" class="input-field" placeholder="Name" required>
            <select name="asset_id" class="input-field">@foreach ($assets as $a)<option value="{{ $a->id }}">{{ $a->symbol }}</option>@endforeach</select>
            <input type="number" step="0.01" name="hashrate_th" class="input-field" placeholder="Hashrate TH/s" required>
            <input type="number" name="term_days" class="input-field" placeholder="Term days" required>
            <input type="number" step="0.01" name="maintenance_fee_pct" class="input-field" placeholder="Maintenance fee %" required>
            <input type="number" step="0.01" name="price" class="input-field" placeholder="Price" required>
            <input type="number" step="0.0001" name="estimated_daily_reward_pct" class="input-field" placeholder="Daily reward %" required>
            <button class="btn-brand">Create</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Asset</th><th>Hashrate</th><th>Term</th><th>Contracts</th><th>Published</th><th></th></tr></thead>
            <tbody>
                @foreach ($packages as $p)
                    <tr>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->asset->symbol }}</td>
                        <td class="font-numeric">{{ $p->hashrate_th }} TH/s</td>
                        <td>{{ $p->term_days }}d</td>
                        <td class="font-numeric">{{ $p->contracts_count }}</td>
                        <td><span class="pill-{{ $p->is_published ? 'success' : 'muted' }}">{{ $p->is_published ? 'Published' : 'Hidden' }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.mining.update', $p) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="is_published" value="{{ $p->is_published ? 0 : 1 }}">
                                <button class="text-xs text-brand hover:underline">{{ $p->is_published ? 'Unpublish' : 'Publish' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
