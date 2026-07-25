@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Mining Rewards Log</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Contract</th><th>Amount</th></tr></thead>
            <tbody>
                @forelse ($rewards as $r)
                    <tr>
                        <td class="text-text-muted">{{ $r->credited_at->format('M d, Y') }}</td>
                        <td>{{ $r->contract->package->name }}</td>
                        <td class="font-numeric price-up">+{{ number_format($r->amount, 8) }} {{ $r->contract->package->asset->symbol }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-text-muted">No rewards credited yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
