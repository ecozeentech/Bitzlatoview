@extends('layouts.app-shell')
@section('title', 'Futures')
@section('content')

<h1 class="text-2xl font-bold mb-2">Futures</h1>
<p class="risk-banner mb-6">Futures can liquidate your margin. Simulation only unless licensed. KYC + agreement required.</p>
@if(!$hasAgreement)
<form method="POST" action="{{ route('app.futures.agreement') }}" class="glass-card mb-6 p-4">@csrf
<p class="text-sm mb-3">I understand futures trading is high risk and may result in loss greater than initial margin.</p>
<button class="btn-brand">Accept futures agreement</button>
</form>
@endif
<div class="grid gap-4 md:grid-cols-2">
@foreach($markets as $m)
<form method="POST" action="{{ route('app.futures.order') }}" class="glass-card space-y-3 p-5">@csrf
<input type="hidden" name="futures_market_id" value="{{ $m->id }}">
<h3 class="font-semibold">{{ $m->symbol }}</h3>
<p class="font-mono text-sm">Mark {{ $m->mark_price }} · Funding {{ $m->funding_rate }}</p>
<select name="side" class="input-field"><option value="long">Long</option><option value="short">Short</option></select>
<select name="margin_mode" class="input-field"><option value="isolated">Isolated</option><option value="cross">Cross</option></select>
<input type="number" name="leverage" class="input-field" value="5" min="1" max="{{ $m->max_leverage }}">
<input type="number" step="any" name="size" class="input-field" placeholder="Size" required>
<button class="btn-brand">Open position</button>
</form>
@endforeach
</div>
<div class="glass-card mt-6 p-4"><h3 class="font-semibold mb-3">Open positions</h3>@foreach($positions as $p)<div class="text-sm border-b border-border/40 py-2 font-mono">{{ $p->side }} size {{ $p->size }} lev {{ $p->leverage }}x liq {{ $p->liquidation_price }}</div>@endforeach</div>

@endsection
