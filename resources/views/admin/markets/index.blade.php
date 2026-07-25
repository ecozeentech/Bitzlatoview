@extends('layouts.admin')
@section('title', 'Markets')
@section('content')
<div class="grid gap-4 md:grid-cols-2"><div class="glass-card p-4"><h3 class="font-semibold mb-3">Pairs</h3>@foreach($pairs as $p)<div class="text-sm border-b border-border/40 py-2">{{ $p->symbol }} · {{ $p->last_price }}</div>@endforeach</div><div class="glass-card p-4"><h3 class="font-semibold mb-3">Assets</h3>@foreach($assets as $a)<div class="text-sm border-b border-border/40 py-2">{{ $a->symbol }} · ${{ $a->mock_price_usd }}</div>@endforeach</div></div>
@endsection
