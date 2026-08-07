@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Security</h1>

    <div class="glass-card p-6">
        @include('profile.partials.update-password-form')
    </div>

    <div class="glass-card p-6">
        <h2 class="mb-2 text-lg font-medium">Two-Factor Authentication</h2>
        @if (auth()->user()->two_factor_enabled)
            <p class="text-sm text-success">2FA is enabled on your account.</p>
            <form method="POST" action="{{ route('app.settings.2fa.disable') }}" class="mt-3">
                @csrf
                <button class="btn-outline text-sm">Disable 2FA</button>
            </form>
        @else
            <p class="text-sm text-text-muted">Scan this secret into Google Authenticator, Authy or 1Password, then enter the 6-digit code to enable 2FA. Required in production for withdrawals, card issuance and security changes.</p>
            <div class="mt-3 rounded-lg border border-border bg-surface-2 p-3 text-center">
                <p class="text-xs text-text-muted">Manual entry secret</p>
                <p class="font-numeric text-lg tracking-widest">{{ $pendingSecret }}</p>
            </div>
            <form method="POST" action="{{ route('app.settings.2fa.enable') }}" class="mt-3 flex gap-2">
                @csrf
                <input type="text" name="code" class="input-field" placeholder="6-digit code" maxlength="6" required>
                <button class="btn-brand text-sm">Verify &amp; Enable</button>
            </form>
        @endif
    </div>

    <div class="glass-card p-6">
        <h2 class="mb-3 text-lg font-medium">Active Sessions</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Device</th><th>IP</th><th>Location</th><th>Last Seen</th></tr></thead>
            <tbody>
                @forelse ($sessions as $s)
                    <tr>
                        <td>{{ $s->device_name ?? 'Unknown device' }}</td>
                        <td class="text-text-muted">{{ $s->ip_address }}</td>
                        <td class="text-text-muted">{{ $s->location ?? '—' }}</td>
                        <td class="text-text-muted">{{ $s->last_seen_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-text-muted">No device sessions recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
