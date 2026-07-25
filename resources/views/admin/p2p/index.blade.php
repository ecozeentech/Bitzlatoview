@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">P2P Overview</h1>
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="glass-card p-5"><p class="text-xs text-text-muted">Active Ads</p><p class="font-numeric text-2xl font-bold">{{ $stats['active_ads'] }}</p></div>
        <div class="glass-card p-5"><p class="text-xs text-text-muted">Open Orders</p><p class="font-numeric text-2xl font-bold">{{ $stats['open_orders'] }}</p></div>
        <div class="glass-card p-5"><p class="text-xs text-text-muted">Open Appeals</p><p class="font-numeric text-2xl font-bold text-danger">{{ $stats['open_appeals'] }}</p></div>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.p2p.orders') }}" class="btn-outline text-sm">View Orders</a>
        <a href="{{ route('admin.p2p.ads') }}" class="btn-outline text-sm">View Ads</a>
        <a href="{{ route('admin.p2p.appeals') }}" class="btn-outline text-sm">View Appeals</a>
    </div>
</div>
@endsection
