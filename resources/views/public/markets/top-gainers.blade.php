@extends('layouts.public')
@section('title', 'Top Gainers')
@section('content')

<div class="page-shell py-16"><h1 class="section-title">Top Gainers</h1>
<div class="mt-8 grid gap-6 lg:grid-cols-3">
<div class="glass-card p-4 lg:col-span-1"><h3 class="font-semibold mb-3">Crypto</h3>@foreach($pairs as $p)<div class="flex justify-between border-b border-border/50 py-2 text-sm"><span>{{ $p->symbol }}</span><span class="price-up">{{ number_format($p->change_24h,2) }}%</span></div>@endforeach</div>
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Stocks</h3>@foreach($stocks as $s)<div class="flex justify-between border-b border-border/50 py-2 text-sm"><span>{{ $s->symbol }}</span><span class="price-up">{{ number_format($s->change_24h,2) }}%</span></div>@endforeach</div>
<div class="glass-card p-4"><h3 class="font-semibold mb-3">NFT Collections</h3>@foreach($nfts as $n)<div class="flex justify-between border-b border-border/50 py-2 text-sm"><span>{{ $n->name }}</span><span class="font-mono">{{ number_format($n->volume_24h,2) }}</span></div>@endforeach</div>
</div></div>
@endsection
