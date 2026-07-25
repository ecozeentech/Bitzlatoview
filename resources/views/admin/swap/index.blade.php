@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Swap Activity</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>From</th><th>To</th><th>Rate</th><th>Fee</th><th>Date</th></tr></thead>
            <tbody>
                @forelse ($swaps as $s)
                    <tr>
                        <td>{{ $s->user->email }}</td>
                        <td class="font-numeric">{{ number_format($s->from_amount, 6) }} {{ $s->fromAsset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($s->to_amount, 6) }} {{ $s->toAsset->symbol }}</td>
                        <td class="font-numeric text-text-muted">{{ number_format($s->rate, 4) }}</td>
                        <td class="font-numeric text-text-muted">{{ number_format($s->fee, 4) }}</td>
                        <td class="text-text-muted">{{ $s->created_at->format('M d, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No swaps yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $swaps->links() }}
</div>
@endsection
