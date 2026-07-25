@extends('layouts.public')
@section('title', 'Top Losers')
@section('content')

<div class="page-shell py-16"><h1 class="section-title">Top Losers</h1>
<div class="glass-card mt-8 p-4">@foreach($pairs as $p)<div class="flex justify-between border-b border-border/50 py-3 text-sm"><span>{{ $p->symbol }}</span><span class="price-down">{{ number_format($p->change_24h,2) }}%</span></div>@endforeach</div></div>
@endsection
