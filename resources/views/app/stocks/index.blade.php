@extends('layouts.app-shell')
@section('title', 'Stocks')
@section('content')

<h1 class="text-2xl font-bold mb-2">Stock trading</h1>
<p class="risk-banner mb-6">Paper trading only. Not a licensed brokerage.</p>
<div class="grid gap-6 lg:grid-cols-2">
<div class="glass-card p-4"><table class="data-table"><thead><tr><th>Symbol</th><th>Price</th><th>Change</th><th></th></tr></thead><tbody>
@foreach($stocks as $s)
<tr><td>{{ $s->symbol }}</td><td class="font-mono">{{ $s->last_price }}</td><td class="{{ $s->change_24h>=0?'price-up':'price-down' }}">{{ $s->change_24h }}%</td>
<td><form method="POST" action="{{ route('app.stocks.order') }}" class="flex gap-1">@csrf<input type="hidden" name="stock_instrument_id" value="{{ $s->id }}"><input type="hidden" name="side" value="buy"><input type="number" step="any" name="quantity" class="input-field !w-20" value="1"><button class="btn-brand text-xs">Buy</button></form></td></tr>
@endforeach
</tbody></table></div>
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Holdings</h3>@foreach($positions as $p)<div class="border-b border-border/40 py-2 text-sm">{{ $p->stockInstrument->symbol ?? '-' }} · {{ $p->quantity }} @ avg {{ $p->avg_cost }}</div>@endforeach</div>
</div>

@endsection
