@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Email Logs</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Recipient</th><th>Subject</th><th>Template</th><th>Status</th><th>Sent</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->recipient }}</td>
                        <td class="text-text-muted">{{ $log->subject }}</td>
                        <td>{{ $log->template_key }}</td>
                        <td><span class="pill-{{ $log->status === 'sent' || $log->status === 'delivered' ? 'success' : 'danger' }}">{{ $log->status }}</span></td>
                        <td class="text-text-muted">{{ $log->sent_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No emails logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection
