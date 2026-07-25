@extends('layouts.app-shell')
@section('title', 'Security')
@section('content')
<h1 class="text-2xl font-bold mb-6">Security</h1><div class="grid gap-4 lg:grid-cols-2"><form method="POST" action="{{ route('app.settings.password') }}" class="glass-card space-y-3 p-5">@csrf<h3 class="font-semibold">Change password</h3><input type="password" name="current_password" class="input-field" placeholder="Current" required><input type="password" name="password" class="input-field" placeholder="New" required><input type="password" name="password_confirmation" class="input-field" placeholder="Confirm" required><button class="btn-brand">Update</button></form><form method="POST" action="{{ route('app.settings.2fa') }}" class="glass-card space-y-3 p-5">@csrf<h3 class="font-semibold">Two-factor auth</h3><p class="text-sm text-muted">Status: {{ auth()->user()->two_factor_enabled ? 'Enabled' : 'Disabled' }}</p><button class="btn-outline">Enable 2FA</button></form></div>
@endsection
