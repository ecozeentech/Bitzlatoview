@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Withdrawals</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Asset</th><th>Amount</th><th>Address</th><th>Status</th><th>Note</th><th></th></tr></thead>
            <tbody>
                @forelse ($withdrawals as $w)
                    <tr>
                        <td>{{ $w->user->email }}</td>
                        <td>{{ $w->asset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($w->amount, 8) }}</td>
                        <td class="text-text-muted text-xs">{{ \Illuminate\Support\Str::limit($w->address, 16) }}</td>
                        <td><span class="pill-{{ $w->status === 'completed' ? 'success' : ($w->status === 'rejected' ? 'danger' : 'warning') }}">{{ str_replace('_',' ',$w->status) }}</span></td>
                        <td class="text-text-muted">{{ $w->user_note }}</td>
                        <td>
                            @if ($w->status === 'pending_review')
                                <form method="POST" action="{{ route('admin.withdrawals.approve', $w) }}" class="inline">@csrf<button class="text-xs text-brand hover:underline">Approve &amp; Complete</button></form>
                                <form method="POST" action="{{ route('admin.withdrawals.reject', $w) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="rejection_reason" value="Rejected by compliance review.">
                                    <button class="text-xs text-danger hover:underline">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-text-muted">No withdrawals yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $withdrawals->links() }}
</div>
@endsection
