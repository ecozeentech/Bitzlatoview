@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Balance Adjustments (Maker/Checker)</h1>
    <div class="risk-banner">No direct balance editing. Every adjustment requires a reason, optional evidence, and a second admin's approval before it posts to the ledger.</div>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Request Adjustment</h2>
        <form method="POST" action="{{ route('admin.adjustments.store') }}" class="grid gap-3 sm:grid-cols-3">
            @csrf
            <select name="user_id" class="input-field">
                @foreach ($users as $u)<option value="{{ $u->id }}">{{ $u->email }}</option>@endforeach
            </select>
            <select name="wallet_type" class="input-field"><option value="primary">Primary</option><option value="trading">Trading</option><option value="investment">Investment</option></select>
            <select name="asset_id" class="input-field">
                @foreach ($assets as $a)<option value="{{ $a->id }}">{{ $a->symbol }}</option>@endforeach
            </select>
            <select name="direction" class="input-field"><option value="credit">Credit</option><option value="debit">Debit</option></select>
            <input type="number" step="0.00000001" name="amount" class="input-field" placeholder="Amount" required>
            <input type="url" name="evidence_url" class="input-field" placeholder="Evidence URL (optional)">
            <textarea name="reason" class="input-field sm:col-span-3" placeholder="Reason (required, audited)" rows="2" required></textarea>
            <button class="btn-brand sm:col-span-3">Request Adjustment</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Direction</th><th>Amount</th><th>Reason</th><th>Status</th><th>Requested By</th><th></th></tr></thead>
            <tbody>
                @forelse ($adjustments as $adj)
                    <tr>
                        <td>{{ $adj->user->email }}</td>
                        <td class="{{ $adj->direction === 'credit' ? 'price-up' : 'price-down' }}">{{ ucfirst($adj->direction) }}</td>
                        <td class="font-numeric">{{ number_format($adj->amount, 8) }} {{ $adj->asset->symbol }}</td>
                        <td class="text-text-muted text-xs">{{ \Illuminate\Support\Str::limit($adj->reason, 40) }}</td>
                        <td><span class="pill-{{ $adj->status === 'applied' ? 'success' : ($adj->status === 'rejected' ? 'danger' : 'warning') }}">{{ str_replace('_',' ',$adj->status) }}</span></td>
                        <td class="text-text-muted">{{ $adj->requestedBy->email }}</td>
                        <td>
                            @if ($adj->status === 'pending_approval')
                                <form method="POST" action="{{ route('admin.adjustments.approve', $adj) }}" class="inline">@csrf<button class="text-xs text-brand hover:underline">Approve</button></form>
                                <form method="POST" action="{{ route('admin.adjustments.reject', $adj) }}" class="inline">@csrf<button class="text-xs text-danger hover:underline">Reject</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-text-muted">No adjustments requested yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $adjustments->links() }}
</div>
@endsection
