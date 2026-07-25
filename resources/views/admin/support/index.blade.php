@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Support Tickets</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Subject</th><th>Category</th><th>Status</th><th>Priority</th><th></th></tr></thead>
            <tbody>
                @forelse ($tickets as $t)
                    <tr>
                        <td>{{ $t->user->email }}</td>
                        <td>{{ $t->subject }}</td>
                        <td>{{ ucfirst($t->category) }}</td>
                        <td><span class="pill-{{ $t->status === 'closed' || $t->status === 'resolved' ? 'muted' : 'warning' }}">{{ $t->status }}</span></td>
                        <td>{{ ucfirst($t->priority) }}</td>
                        <td><a href="{{ route('admin.support.show', $t) }}" class="text-xs text-brand hover:underline">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No tickets yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $tickets->links() }}
</div>
@endsection
