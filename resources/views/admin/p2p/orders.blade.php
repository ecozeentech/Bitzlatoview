@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">P2P Orders</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>#</th><th>Buyer</th><th>Seller</th><th>Asset</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr>
                        <td>#{{ $o->id }}</td>
                        <td>{{ $o->buyer->email }}</td>
                        <td>{{ $o->seller->email }}</td>
                        <td>{{ $o->asset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($o->crypto_amount, 6) }}</td>
                        <td><span class="pill-{{ $o->status === 'completed' ? 'success' : ($o->status === 'cancelled' ? 'danger' : 'warning') }}">{{ str_replace('_',' ',$o->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
@endsection
