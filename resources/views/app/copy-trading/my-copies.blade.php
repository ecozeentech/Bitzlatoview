@extends('layouts.app-shell')
@section('title', 'My Copies')
@section('content')

<h1 class="text-2xl font-bold mb-6">My copy allocations</h1>
@foreach($allocations as $a)
<div class="glass-card mb-3 flex flex-wrap items-center justify-between gap-3 p-4">
  <div><p class="font-semibold">{{ $a->copyTraderProfile->display_name ?? 'Trader' }}</p><p class="text-sm text-muted">{{ $a->status }} · {{ $a->allocation_amount }} · PnL {{ $a->pnl }}</p></div>
  <div class="flex gap-2">
  <form method="POST" action="{{ route('app.copy-trading.pause',$a->id) }}">@csrf<button class="btn-outline text-xs">Pause</button></form>
  <form method="POST" action="{{ route('app.copy-trading.stop',$a->id) }}">@csrf<button class="btn-ghost text-xs text-danger">Stop</button></form>
  </div>
</div>
@endforeach

@endsection
