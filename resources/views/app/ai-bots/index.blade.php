@extends('layouts.app-shell')
@section('title', 'AI Bots')
@section('content')

<h1 class="text-2xl font-bold mb-2">AI Trading Bots</h1>
<p class="risk-banner mb-6">AI bots are experimental and may lose money. Paper mode by default.</p>
<div class="mb-4 flex gap-2"><a class="btn-outline text-xs" href="{{ route('app.ai-bots.marketplace') }}">Marketplace</a><a class="btn-outline text-xs" href="{{ route('app.ai-bots.my-bots') }}">My bots</a></div>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
@foreach($bots as $b)
<div class="glass-card p-5"><h3 class="font-semibold">{{ $b->name }}</h3><p class="text-sm text-purple mt-1">{{ $b->strategy_type }} · risk {{ $b->risk_score }}</p>
<p class="text-sm text-muted mt-2">Sim 30d {{ $b->simulated_return_30d }}% · min {{ $b->min_allocation }}</p>
<form method="POST" action="{{ route('app.ai-bots.allocate',$b->id) }}" class="mt-4 flex gap-2">@csrf<input type="number" step="any" name="amount" class="input-field" required><button class="btn-brand text-xs">Start</button></form></div>
@endforeach
</div>

@endsection
