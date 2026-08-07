@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Withdrawal Requests</h1>
    <div class="risk-banner">Two-step manual control: "Approve" only confirms the request is legitimate — funds stay locked. Only "Mark Completed" (after you have actually sent the money externally) debits the user's wallet.</div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Asset</th><th>Amount</th><th>Send Via</th><th>Destination</th><th>Status</th><th>Note</th><th></th></tr></thead>
            <tbody>
                @forelse ($withdrawals as $w)
                    <tr>
                        <td>{{ $w->user->email }}</td>
                        <td>{{ $w->asset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($w->amount, 8) }}</td>
                        <td class="text-text-muted">{{ $w->payment_method_type ? str_replace('_', ' ', $w->payment_method_type) : '—' }}</td>
                        <td class="text-text-muted text-xs">
                            {{ \Illuminate\Support\Str::limit($w->address, 24) }}
                            @if ($w->destination_details)
                                <br>{{ \Illuminate\Support\Str::limit($w->destination_details, 60) }}
                            @endif
                        </td>
                        <td><span class="pill-{{ $w->status === 'completed' ? 'success' : ($w->status === 'rejected' ? 'danger' : 'warning') }}">{{ str_replace('_',' ',$w->status) }}</span></td>
                        <td class="text-text-muted">{{ $w->admin_note ?? $w->user_note }}</td>
                        <td class="space-x-2">
                            @if ($w->status === 'pending_review')
                                <form method="POST" action="{{ route('admin.withdrawals.approve', $w) }}" class="inline">@csrf<button class="text-xs text-brand hover:underline">Approve</button></form>
                            @endif
                            @if (in_array($w->status, ['pending_review', 'approved']))
                                <form method="POST" action="{{ route('admin.withdrawals.complete', $w) }}" class="inline">@csrf<button class="text-xs text-success hover:underline">Mark Completed</button></form>
                                <form method="POST" action="{{ route('admin.withdrawals.reject', $w) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="rejection_reason" value="Rejected by compliance review.">
                                    <button class="text-xs text-danger hover:underline">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-text-muted">No withdrawals yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $withdrawals->links() }}
</div>
@endsection
