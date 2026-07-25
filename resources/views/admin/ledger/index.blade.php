@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Ledger Transactions</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Reference</th><th>Description</th><th>Entries</th></tr></thead>
            <tbody>
                @forelse ($transactions as $t)
                    <tr>
                        <td class="text-text-muted">{{ $t->created_at->format('M d, H:i:s') }}</td>
                        <td>{{ $t->reference_type }} @if($t->reference_id) #{{ $t->reference_id }} @endif</td>
                        <td class="text-text-muted">{{ $t->description }}</td>
                        <td class="text-xs">
                            @foreach ($t->entries as $e)
                                <div>{{ $e->walletAccount->user->email ?? 'house' }} · {{ ucfirst($e->direction) }} {{ number_format($e->amount, 6) }} {{ $e->asset->symbol }}</div>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-text-muted">No ledger transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $transactions->links() }}
</div>
@endsection
