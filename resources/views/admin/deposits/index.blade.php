@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Deposit Requests</h1>
    <div class="risk-banner">Verify the uploaded proof of payment against the payment method the user selected before crediting. Crediting posts a real ledger entry — there is no way to credit a wallet without going through this review.</div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Ref</th><th>User</th><th>Payment Method</th><th>Asset</th><th>Amount</th><th>Proof</th><th>Status</th><th>Note</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse ($deposits as $d)
                    <tr>
                        <td class="font-numeric text-xs">{{ $d->reference_code }}</td>
                        <td>{{ $d->user->email }}</td>
                        <td class="text-text-muted">{{ $d->paymentMethod?->label() ?? '—' }}</td>
                        <td>{{ $d->asset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($d->amount, 8) }}</td>
                        <td>
                            @if ($d->proof_file_path)
                                <a href="{{ route('admin.deposits.proof', $d) }}" target="_blank" class="text-xs text-brand hover:underline">View proof</a>
                            @else
                                <span class="text-xs text-text-muted">None</span>
                            @endif
                        </td>
                        <td><span class="pill-{{ $d->status === 'credited' ? 'success' : ($d->status === 'rejected' ? 'danger' : 'warning') }}">{{ $d->status }}</span></td>
                        <td class="text-text-muted text-xs">{{ $d->user_note }}</td>
                        <td class="text-text-muted">{{ $d->created_at->format('M d, H:i') }}</td>
                        <td>
                            @if ($d->status === 'pending')
                                <details>
                                    <summary class="cursor-pointer text-xs text-brand">Review</summary>
                                    <form method="POST" action="{{ route('admin.deposits.credit', $d) }}" class="mt-2 space-y-2">
                                        @csrf
                                        <input type="number" step="0.00000001" name="credited_amount" class="input-field w-40 text-xs" placeholder="Amount to credit" value="{{ $d->amount }}">
                                        <input type="text" name="admin_note" class="input-field w-56 text-xs" placeholder="Verification note">
                                        <button class="btn-brand text-xs">Confirm &amp; Credit</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.deposits.reject', $d) }}" class="mt-2">
                                        @csrf
                                        <input type="text" name="rejection_reason" class="input-field w-56 text-xs" placeholder="Rejection reason" required>
                                        <button class="text-xs text-danger hover:underline">Reject</button>
                                    </form>
                                </details>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-text-muted">No deposit requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $deposits->links() }}
</div>
@endsection
