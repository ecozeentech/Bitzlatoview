@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Investment Products</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Asset</th><th>APY</th><th>Lock</th><th>Subscriptions</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($products as $p)
                    <tr>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->asset->symbol }}</td>
                        <td class="font-numeric price-up">{{ $p->apy_pct }}%</td>
                        <td>{{ $p->lock_days }}d</td>
                        <td class="font-numeric">{{ $p->subscriptions_count }}</td>
                        <td><span class="pill-{{ $p->status === 'active' ? 'success' : 'muted' }}">{{ $p->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
