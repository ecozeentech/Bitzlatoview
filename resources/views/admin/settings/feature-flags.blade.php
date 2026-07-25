@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Feature Flags</h1>
    <div class="risk-banner">Use feature flags to disable risky modules (futures, mining, cards, etc.) platform-wide without a deploy.</div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Key</th><th>Name</th><th>Description</th><th>Enabled</th><th></th></tr></thead>
            <tbody>
                @forelse ($flags as $flag)
                    <tr>
                        <td class="font-numeric">{{ $flag->key }}</td>
                        <td>{{ $flag->name }}</td>
                        <td class="text-text-muted">{{ $flag->description }}</td>
                        <td><span class="pill-{{ $flag->is_enabled ? 'success' : 'muted' }}">{{ $flag->is_enabled ? 'Enabled' : 'Disabled' }}</span></td>
                        <td><form method="POST" action="{{ route('admin.settings.feature-flags.toggle', $flag) }}">@csrf<button class="text-xs text-brand hover:underline">Toggle</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No feature flags configured yet. Run the seeder to add defaults.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
