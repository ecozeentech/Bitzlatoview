@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <h1 class="text-2xl font-bold">P2P Merchant</h1>

    @if ($profile)
        <div class="glass-card p-5">
            <p class="font-semibold">{{ $profile->display_name }} @if ($profile->is_verified) <span class="pill-info">Verified</span> @else <span class="pill-warning">Pending Verification</span> @endif</p>
            <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-text-muted">
                <div>Completed orders: <span class="text-text-main">{{ $profile->completed_orders }}</span></div>
                <div>Completion rate: <span class="text-text-main">{{ $profile->completion_rate }}%</span></div>
                <div>Positive feedback: <span class="text-text-main">{{ $profile->positive_feedback_rate }}%</span></div>
                <div>Avg release time: <span class="text-text-main">{{ $profile->avg_release_minutes }} min</span></div>
            </div>
        </div>
    @else
        <div class="glass-card p-5">
            <p class="text-sm text-text-muted">Merchant status unlocks higher visibility ads and a verified badge. Requires approved KYC. Applications are reviewed by our compliance team.</p>
            <form method="POST" action="{{ route('app.p2p.merchant.apply') }}" class="mt-4 space-y-3">
                @csrf
                <input type="text" name="display_name" class="input-field" placeholder="Merchant display name" required>
                <button class="btn-brand">Apply for Merchant Status</button>
            </form>
        </div>
    @endif
</div>
@endsection
