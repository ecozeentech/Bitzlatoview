@extends('layouts.app-shell')
@section('title', 'Markets')
@section('content')

<h1 class="text-2xl font-bold mb-6">Markets</h1>
<div class="glass-card p-4 overflow-x-auto"><table class="data-table"><thead><tr><th>Pair</th><th>Price</th><th>Change</th><th></th></tr></thead><tbody>
@foreach($pairs as $p)<tr><td>{{ $p->symbol }}</td><td class="font-mono">{{ number_format($p->last_price,4) }}</td><td class="{{ $p->change_24h>=0?'price-up':'price-down' }}">{{ number_format($p->change_24h,2) }}%</td><td><a class="text-brand" href="{{ route('app.spot.show',$p->symbol) }}">Trade</a></td></tr>@endforeach
</tbody></table></div>

@endsection
