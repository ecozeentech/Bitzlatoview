@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Deposits</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Asset</th><th>Amount</th><th>Status</th><th>Note</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse ($deposits as $d)
                    <tr>
                        <td>{{ $d->user->email }}</td>
                        <td>{{ $d->asset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($d->amount, 8) }}</td>
                        <td><span class="pill-{{ $d->status === 'credited' ? 'success' : ($d->status === 'rejected' ? 'danger' : 'warning') }}">{{ $d->status }}</span></td>
                        <td class="text-text-muted">{{ $d->user_note }}</td>
                        <td class="text-text-muted">{{ $d->created_at->format('M d, H:i') }}</td>
                        <td>
                            @if ($d->status === 'pending')
                                <form method="POST" action="{{ route('admin.deposits.credit', $d) }}" class="inline">@csrf<button class="text-xs text-brand hover:underline">Credit</button></form>
                                <form method="POST" action="{{ route('admin.deposits.reject', $d) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="rejection_reason" value="Rejected by compliance review.">
                                    <button class="text-xs text-danger hover:underline">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-text-muted">No deposits yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $deposits->links() }}
</div>
@endsection
