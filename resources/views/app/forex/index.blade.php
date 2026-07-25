@extends('layouts.app-shell')
@section('title', 'Forex')
@section('content')

<h1 class="text-2xl font-bold mb-2">Forex</h1>
<p class="risk-banner mb-6">Paper FX trading with leverage placeholders. High risk.</p>
<div class="grid gap-6 lg:grid-cols-2">
<div class="glass-card p-4 space-y-3">
@foreach($pairs as $p)
<form method="POST" action="{{ route('app.forex.order') }}" class="flex flex-wrap items-center justify-between gap-2 border-b border-border/40 py-3">@csrf
<input type="hidden" name="forex_pair_id" value="{{ $p->id }}">
<div><p class="font-semibold">{{ $p->symbol }}</p><p class="font-mono text-xs">{{ $p->bid }} / {{ $p->ask }} · spread {{ $p->spread }}</p></div>
<div class="flex gap-2"><input type="number" step="0.01" name="lots" value="0.01" class="input-field !w-24"><select name="side" class="input-field !w-24"><option value="buy">Buy</option><option value="sell">Sell</option></select><button class="btn-brand text-xs">Open</button></div>
</form>
@endforeach
</div>
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Open positions</h3>@foreach($positions as $pos)<div class="text-sm border-b border-border/40 py-2">{{ $pos->forexPair->symbol ?? '-' }} {{ $pos->side }} {{ $pos->lots }} @ {{ $pos->entry_price }}</div>@endforeach</div>
</div>

@endsection
