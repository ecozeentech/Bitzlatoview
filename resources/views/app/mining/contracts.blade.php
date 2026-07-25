@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">My Mining Contracts</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Package</th><th>Invested</th><th>Total Rewards</th><th>Term</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($contracts as $c)
                    <tr>
                        <td class="flex items-center gap-2"><x-asset-icon :symbol="$c->package->asset->symbol" /> {{ $c->package->name }}</td>
                        <td class="font-numeric">${{ number_format($c->amount_invested, 2) }}</td>
                        <td class="font-numeric price-up">{{ number_format($c->rewards->sum('amount'), 8) }} {{ $c->package->asset->symbol }}</td>
                        <td class="text-text-muted">{{ $c->start_date->format('M d') }} – {{ $c->end_date->format('M d, Y') }}</td>
                        <td><span class="pill-{{ $c->status === 'active' ? 'success' : 'muted' }}">{{ $c->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No mining contracts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
