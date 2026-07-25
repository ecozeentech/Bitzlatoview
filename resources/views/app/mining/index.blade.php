@extends('layouts.app-shell')
@section('title', 'Mining')
@section('content')

<h1 class="text-2xl font-bold mb-2">Mining contracts</h1>
<p class="risk-banner mb-6">Estimated rewards are simulated. Returns are not guaranteed. Maintenance fees may apply.</p>
<div class="mb-4 flex gap-2"><a href="{{ route('app.mining.contracts') }}" class="btn-outline text-xs">My contracts</a><a href="{{ route('app.mining.rewards') }}" class="btn-outline text-xs">Rewards</a></div>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
@foreach($packages as $p)
<div class="glass-card p-5"><h3 class="font-semibold">{{ $p->name }}</h3>
<p class="text-sm text-muted mt-2">{{ $p->hashrate }} {{ $p->hashrate_unit }} · {{ $p->term_days }} days</p>
<p class="font-mono text-sm mt-2">Price {{ $p->price }} · Est. daily {{ $p->estimated_daily_reward }}</p>
<form method="POST" action="{{ route('app.mining.purchase',$p->id) }}" class="mt-4">@csrf<button class="btn-brand text-xs">Purchase</button></form></div>
@endforeach
</div>

@endsection
