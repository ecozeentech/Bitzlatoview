@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">KYC Review Queue</h1>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Legal Name</th><th>Country</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
            <tbody>
                @forelse ($submissions as $s)
                    <tr>
                        <td>{{ $s->user->email }}</td>
                        <td>{{ $s->legal_name }}</td>
                        <td class="text-text-muted">{{ $s->country }}</td>
                        <td><span class="pill-warning">{{ str_replace('_',' ',$s->status) }}</span></td>
                        <td class="text-text-muted">{{ $s->submitted_at?->diffForHumans() }}</td>
                        <td><a href="{{ route('admin.kyc.show', $s) }}" class="text-xs text-brand hover:underline">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No pending submissions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $submissions->links() }}

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Recently Decided</h2>
        <table class="data-table">
            <thead><tr><th>User</th><th>Decision</th><th>Date</th></tr></thead>
            <tbody>
                @foreach ($recentlyDecided as $s)
                    <tr>
                        <td>{{ $s->user->email }}</td>
                        <td><span class="pill-{{ $s->status === 'approved' ? 'success' : 'danger' }}">{{ $s->status }}</span></td>
                        <td class="text-text-muted">{{ $s->reviewed_at?->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
