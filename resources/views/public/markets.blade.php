@extends('layouts.public')
@section('title', 'Markets')
@section('content')

<div class="page-shell py-12">
<h1 class="section-title">Crypto Markets</h1>
<p class="section-sub">Simulated spot pairs with 24h stats.</p>
<div class="glass-card mt-8 overflow-x-auto p-4">
<table class="data-table"><thead><tr><th>Pair</th><th>Price</th><th>24h</th><th>Volume</th><th></th></tr></thead><tbody>
@foreach($pairs as $p)
<tr>
<td class="font-medium">{{ $p->symbol }}</td>
<td class="font-mono">{{ number_format($p->last_price, 4) }}</td>
<td class="{{ $p->change_24h >= 0 ? 'price-up' : 'price-down' }}">{{ number_format($p->change_24h, 2) }}%</td>
<td class="font-mono">{{ number_format($p->volume_24h, 0) }}</td>
<td><a class="text-brand text-sm" href="{{ route('app.spot.show', $p->symbol) }}">Trade</a></td>
</tr>
@endforeach
</tbody></table>
<div class="mt-4">{{ $pairs->links() }}</div>
</div></div>
@endsection
