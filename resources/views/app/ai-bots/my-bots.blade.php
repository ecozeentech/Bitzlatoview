@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">My AI Bot Allocations</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Bot</th><th>Amount</th><th>P&amp;L</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($allocations as $a)
                    <tr>
                        <td><a href="{{ route('app.ai-bots.show', $a->bot) }}" class="text-brand hover:underline">{{ $a->bot->name }}</a></td>
                        <td class="font-numeric">${{ number_format($a->amount, 2) }}</td>
                        <td class="font-numeric {{ $a->pnl >= 0 ? 'price-up' : 'price-down' }}">${{ number_format($a->pnl, 2) }}</td>
                        <td><span class="pill-{{ $a->status === 'active' ? 'success' : ($a->status === 'stopped' ? 'muted' : 'warning') }}">{{ $a->status }}</span></td>
                        <td>
                            @if ($a->status !== 'stopped')
                                <form method="POST" action="{{ route('app.ai-bots.stop', $a) }}">@csrf<button class="text-xs text-danger hover:underline">Stop</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No bot allocations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
