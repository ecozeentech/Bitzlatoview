@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Risk &amp; Compliance</h1>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Open Compliance Alerts</h2>
        <table class="data-table">
            <thead><tr><th>User</th><th>Type</th><th>Severity</th><th>Details</th><th></th></tr></thead>
            <tbody>
                @forelse ($alerts as $a)
                    <tr>
                        <td>{{ $a->user->email }}</td>
                        <td>{{ $a->type }}</td>
                        <td><span class="pill-{{ $a->severity === 'critical' || $a->severity === 'high' ? 'danger' : 'warning' }}">{{ $a->severity }}</span></td>
                        <td class="text-text-muted">{{ $a->details }}</td>
                        <td><form method="POST" action="{{ route('admin.risk.alerts.resolve', $a) }}">@csrf<button class="text-xs text-brand hover:underline">Resolve</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No open alerts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Elevated Risk Scores</h2>
        <table class="data-table">
            <thead><tr><th>User</th><th>Score</th><th>Level</th></tr></thead>
            <tbody>
                @forelse ($riskScores as $r)
                    <tr>
                        <td>{{ $r->user->email }}</td>
                        <td class="font-numeric">{{ $r->score }}</td>
                        <td><span class="pill-{{ $r->level === 'high' ? 'danger' : 'warning' }}">{{ $r->level }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-text-muted">No elevated risk scores.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
