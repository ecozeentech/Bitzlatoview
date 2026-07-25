@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Audit Logs</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Target</th><th>IP</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-text-muted">{{ $log->created_at->format('M d, H:i:s') }}</td>
                        <td>{{ $log->actor?->email ?? 'System' }}</td>
                        <td class="font-numeric">{{ $log->action }}</td>
                        <td class="text-text-muted">{{ $log->target_type ? class_basename($log->target_type).' #'.$log->target_id : '—' }}</td>
                        <td class="text-text-muted">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No audit log entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection
