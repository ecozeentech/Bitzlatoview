@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">P2P Appeals</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Order</th><th>Raised By</th><th>Reason</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($appeals as $appeal)
                    <tr>
                        <td>#{{ $appeal->p2p_order_id }} ({{ $appeal->order->buyer->email }} / {{ $appeal->order->seller->email }})</td>
                        <td>{{ $appeal->raisedBy->email }}</td>
                        <td class="text-text-muted text-xs">{{ \Illuminate\Support\Str::limit($appeal->reason, 50) }}</td>
                        <td><span class="pill-{{ $appeal->status === 'resolved' ? 'success' : 'warning' }}">{{ $appeal->status }}</span></td>
                        <td>
                            @if ($appeal->status === 'open')
                                <details>
                                    <summary class="cursor-pointer text-xs text-brand">Resolve</summary>
                                    <form method="POST" action="{{ route('admin.p2p.appeals.resolve', $appeal) }}" class="mt-2 space-y-2">
                                        @csrf
                                        <select name="action" class="input-field text-xs">
                                            <option value="release_to_buyer">Release to buyer</option>
                                            <option value="refund_to_seller">Refund to seller</option>
                                        </select>
                                        <textarea name="resolution" class="input-field text-xs" placeholder="Resolution notes" required></textarea>
                                        <button class="btn-brand text-xs">Submit</button>
                                    </form>
                                </details>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No appeals yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $appeals->links() }}
</div>
@endsection
