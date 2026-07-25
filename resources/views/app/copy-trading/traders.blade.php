@extends('layouts.app-shell')
@section('title', 'Traders')
@section('content')

<h1 class="text-2xl font-bold mb-4">Trader directory</h1>
<div class="mb-4 flex flex-wrap gap-2">
@foreach(['crypto','forex','futures','stock','p2p','all'] as $c)
<a href="?category={{ $c }}" class="btn-outline text-xs {{ $category===$c?'!border-brand text-brand':'' }}">{{ strtoupper($c) }}</a>
@endforeach
</div>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
@foreach($traders as $t)
<div class="glass-card p-5"><h3 class="font-semibold">{{ $t->display_name }} @if($t->is_verified)<span class="badge-success">Verified</span>@endif</h3>
<p class="text-sm text-muted mt-1">{{ $t->strategy }}</p>
<p class="font-mono text-sm mt-2">30d {{ $t->return_30d }}% · 90d {{ $t->return_90d }}%</p></div>
@endforeach
</div>

@endsection
