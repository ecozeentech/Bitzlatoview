@extends('layouts.app-shell')
@section('title', 'P2P Sell')
@section('content')

<h1 class="text-2xl font-bold mb-6">Sell crypto via P2P</h1>
<div class="glass-card p-4">@forelse($ads as $ad)<div class="flex justify-between border-b border-border/40 py-3 text-sm"><span>{{ $ad->asset->symbol }} buyer @ {{ $ad->price }} {{ $ad->fiat_currency }}</span><span class="font-mono">{{ $ad->min_limit }}-{{ $ad->max_limit }}</span></div>@empty<p class="text-muted text-sm">No buy ads yet.</p>@endforelse</div>

@endsection
