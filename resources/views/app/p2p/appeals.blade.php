@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">My P2P Appeals</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Order</th><th>Reason</th><th>Status</th><th>Resolution</th></tr></thead>
            <tbody>
                @forelse ($appeals as $appeal)
                    <tr>
                        <td><a href="{{ route('app.p2p.orders.show', $appeal->order) }}" class="text-brand hover:underline">#{{ $appeal->p2p_order_id }}</a></td>
                        <td class="text-text-muted">{{ \Illuminate\Support\Str::limit($appeal->reason, 60) }}</td>
                        <td><span class="pill-warning">{{ $appeal->status }}</span></td>
                        <td class="text-text-muted">{{ $appeal->resolution ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-text-muted">No appeals raised.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
