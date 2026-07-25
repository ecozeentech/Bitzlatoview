@extends('layouts.app-shell')
@section('title', 'Merchant')
@section('content')

<h1 class="text-2xl font-bold mb-6">Merchant profile</h1>
<div class="glass-card p-6 space-y-2 text-sm">
<p>Status: <span class="text-brand">{{ $profile->status }}</span></p>
<p>Verified: {{ $profile->is_verified ? 'Yes' : 'No (enhanced KYC required)' }}</p>
<p>Completed trades: {{ $profile->completed_trades }}</p>
<p>Completion rate: {{ $profile->completion_rate }}%</p>
<p>Avg release: {{ $profile->avg_release_minutes }} min</p>
</div>

@endsection
