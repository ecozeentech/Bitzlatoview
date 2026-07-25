@extends('layouts.app-shell')
@section('title', 'P2P')
@section('content')

<h1 class="text-2xl font-bold mb-4">P2P Trading</h1>
<p class="risk-banner mb-6">Do not trade outside Bitzlatoview. Payment account name must match verified legal name.</p>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
@foreach([['Buy crypto','app.p2p.buy'],['Sell crypto','app.p2p.sell'],['My orders','app.p2p.orders'],['My ads','app.p2p.ads'],['Merchant','app.p2p.merchant'],['Appeals','app.p2p.appeals']] as [$l,$r])
<a href="{{ route($r) }}" class="glass-card p-5 font-semibold hover:border-brand/40">{{ $l }}</a>
@endforeach
</div>

@endsection
