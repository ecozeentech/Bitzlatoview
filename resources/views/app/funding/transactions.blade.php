@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Transaction History</h1>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Deposits</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Date</th><th>Reference</th><th>Payment Method</th><th>Asset</th><th>Amount</th><th>Status</th><th>Note</th></tr></thead>
                <tbody>
                    @forelse ($deposits as $d)
                        <tr>
                            <td class="text-text-muted">{{ $d->created_at->format('M d, Y H:i') }}</td>
                            <td class="font-numeric text-xs">{{ $d->reference_code }}</td>
                            <td class="text-text-muted">{{ $d->paymentMethod?->label() ?? '—' }}</td>
                            <td>{{ $d->asset->symbol }}</td>
                            <td class="font-numeric">{{ number_format($d->amount, 8) }}</td>
                            <td><span class="pill-{{ $d->status === 'credited' ? 'success' : ($d->status === 'rejected' || $d->status === 'failed' ? 'danger' : 'warning') }}">{{ str_replace('_', ' ', $d->status) }}</span></td>
                            <td class="text-text-muted">{{ $d->admin_note ?? $d->user_note }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-text-muted">No deposits yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Withdrawals</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Date</th><th>Sent Via</th><th>Asset</th><th>Amount</th><th>Fee</th><th>Status</th><th>Note</th></tr></thead>
                <tbody>
                    @forelse ($withdrawals as $w)
                        <tr>
                            <td class="text-text-muted">{{ $w->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-text-muted">{{ $w->payment_method_type ? str_replace('_', ' ', $w->payment_method_type) : '—' }}</td>
                            <td>{{ $w->asset->symbol }}</td>
                            <td class="font-numeric">{{ number_format($w->amount, 8) }}</td>
                            <td class="font-numeric text-text-muted">{{ number_format($w->fee, 8) }}</td>
                            <td><span class="pill-{{ $w->status === 'completed' ? 'success' : ($w->status === 'rejected' || $w->status === 'failed' ? 'danger' : 'warning') }}">{{ str_replace('_',' ',$w->status) }}</span></td>
                            <td class="text-text-muted">{{ $w->admin_note ?? $w->user_note }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-text-muted">No withdrawals yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
