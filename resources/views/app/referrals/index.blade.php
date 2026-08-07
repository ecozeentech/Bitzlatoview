@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Referrals</h1>

    <div class="glass-card p-6">
        <p class="text-sm text-text-muted">Share your referral code to earn rewards when friends sign up and trade.</p>
        <div class="mt-3 flex items-center gap-2">
            <input readonly value="{{ url('/register?ref='.auth()->user()->referral_code) }}" class="input-field font-numeric" id="ref-link">
            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('ref-link').value)" class="btn-outline text-sm">Copy</button>
        </div>
        <p class="mt-2 text-xs text-text-muted">Your code: <span class="font-numeric text-brand">{{ auth()->user()->referral_code }}</span></p>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Referred Users ({{ $referrals->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>User</th><th>Joined</th><th>KYC</th></tr></thead>
            <tbody>
                @forelse ($referrals as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td class="text-text-muted">{{ $r->created_at->format('M d, Y') }}</td>
                        <td><span class="pill-{{ $r->kyc_status === 'approved' ? 'success' : 'warning' }}">{{ str_replace('_',' ',$r->kyc_status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-text-muted">No referrals yet. Share your link to get started.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
