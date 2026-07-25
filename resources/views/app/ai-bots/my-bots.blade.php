@extends('layouts.app-shell')
@section('title', 'My Bots')
@section('content')

<h1 class="text-2xl font-bold mb-6">My bot allocations</h1>
@foreach($allocations as $a)
<div class="glass-card mb-3 flex justify-between p-4"><div><p class="font-semibold">{{ $a->aiBot->name ?? 'Bot' }}</p><p class="text-sm text-muted">{{ $a->status }} · {{ $a->amount }} · PnL {{ $a->pnl }}</p></div>
<div class="flex gap-2"><form method="POST" action="{{ route('app.ai-bots.pause',$a->id) }}">@csrf<button class="btn-outline text-xs">Pause</button></form>
<form method="POST" action="{{ route('app.ai-bots.stop',$a->id) }}">@csrf<button class="btn-ghost text-xs">Stop</button></form></div></div>
@endforeach

@endsection
