@extends('layouts.public')
@section('title', 'Two-Factor')
@section('content')
<div class="page-shell py-16 max-w-md">
    <div class="glass-card p-6">
        <h1 class="text-xl font-bold">Two-factor authentication</h1>
        <p class="mt-2 text-sm text-muted">Enable 2FA from Settings → Security. High-risk actions (withdrawals, cards, security changes) should require 2FA in production.</p>
        <a href="{{ route('app.settings.security') }}" class="btn-brand mt-6">Open security settings</a>
    </div>
</div>
@endsection
