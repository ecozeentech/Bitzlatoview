@extends('layouts.app-shell')
@section('title', 'Copy Trading')
@section('content')

<h1 class="text-2xl font-bold mb-2">Copy Trading</h1>
<p class="risk-banner mb-6">Simulated performance. No guaranteed returns. Allocation locks Investment Wallet funds.</p>
<div class="mb-4 flex gap-3"><a href="{{ route('app.copy-trading.traders') }}" class="btn-outline text-xs">Trader directory</a><a href="{{ route('app.copy-trading.my-copies') }}" class="btn-outline text-xs">My copies</a></div>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
@foreach($traders as $t)
<div class="glass-card p-5">
  <div class="flex justify-between"><h3 class="font-semibold">{{ $t->display_name }}</h3><span class="badge-muted">{{ $t->risk_level }}</span></div>
  <p class="mt-2 text-sm text-muted">{{ $t->category }} · {{ $t->followers }} followers</p>
  <p class="mt-2 font-mono text-sm">30d {{ number_format($t->return_30d,2) }}% · DD {{ number_format($t->max_drawdown,2) }}%</p>
  <form method="POST" action="{{ route('app.copy-trading.allocate',$t->id) }}" class="mt-4 flex gap-2">@csrf
  <input type="number" step="any" name="amount" class="input-field" placeholder="USDT amount" required>
  <button class="btn-brand text-xs">Copy</button></form>
</div>
@endforeach
</div>

@endsection
