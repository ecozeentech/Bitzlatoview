@extends('layouts.app-shell')
@section('title', 'Spot')
@section('content')

<h1 class="text-2xl font-bold mb-6">Spot Trading</h1>
<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
@foreach($pairs as $p)
<a href="{{ route('app.spot.show',$p->symbol) }}" class="glass-card p-4 hover:border-brand/40"><div class="font-semibold">{{ $p->symbol }}</div><div class="font-mono mt-2">{{ number_format($p->last_price,4) }}</div><div class="{{ $p->change_24h>=0?'price-up':'price-down' }} text-sm">{{ number_format($p->change_24h,2) }}%</div></a>
@endforeach
</div>

@endsection
