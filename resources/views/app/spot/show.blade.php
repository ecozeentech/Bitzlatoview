@extends('layouts.app-shell')
@section('title', 'Spot Pair')
@section('content')

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div><h1 class="text-2xl font-bold">{{ $pair->symbol }}</h1><p class="font-mono text-brand text-xl">{{ number_format($pair->last_price,4) }} <span class="text-sm {{ $pair->change_24h>=0?'price-up':'price-down' }}">{{ number_format($pair->change_24h,2) }}%</span></p></div>
  <span class="badge-warning">Simulated matching</span>
</div>
<div class="grid gap-4 xl:grid-cols-3">
  <div class="glass-card p-4 xl:col-span-2">
    <h3 class="font-semibold mb-3">Chart placeholder</h3>
    <div class="flex h-64 items-end gap-1 rounded-xl bg-surface-2 p-4">
      @for($i=0;$i<24;$i++)<div class="flex-1 rounded-t bg-brand/70" style="height: {{ 20 + (($i * 37) % 70) }}%"></div>@endfor
    </div>
    <p class="mt-2 text-xs text-muted">Connect TradingView Lightweight Charts / market data provider here.</p>
  </div>
  <form method="POST" action="{{ route('app.spot.order',$pair->symbol) }}" class="glass-card space-y-3 p-4">@csrf
    <h3 class="font-semibold">Order ticket</h3>
    <select name="side" class="input-field"><option value="buy">Buy</option><option value="sell">Sell</option></select>
    <select name="type" class="input-field"><option value="market">Market</option><option value="limit">Limit</option></select>
    <input name="price" class="input-field" placeholder="Limit price (optional)" step="any" type="number">
    <input name="quantity" class="input-field" placeholder="Quantity" step="any" type="number" required>
    <button class="btn-brand w-full">Place order</button>
    <p class="text-xs text-muted">Uses Trading Wallet. Market fills instantly; limits lock funds.</p>
  </form>
</div>
<div class="mt-6 grid gap-4 lg:grid-cols-2">
  <div class="glass-card p-4"><h3 class="font-semibold mb-3">Open / recent orders</h3>@foreach($orders as $o)<div class="flex justify-between border-b border-border/40 py-2 text-xs"><span>{{ $o->side }} {{ $o->type }} {{ $o->quantity }} · {{ $o->status }}</span>@if($o->status==='new' && $o->type==='limit')<form method="POST" action="{{ route('app.orders.cancel',$o->id) }}">@csrf @method('DELETE')<button class="text-danger">Cancel</button></form>@endif</div>@endforeach</div>
  <div class="glass-card p-4"><h3 class="font-semibold mb-3">Trades</h3>@foreach($trades as $t)<div class="border-b border-border/40 py-2 text-xs font-mono">{{ $t->side }} {{ $t->quantity }} @ {{ $t->price }}</div>@endforeach</div>
</div>

@endsection
