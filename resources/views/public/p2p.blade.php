@extends('layouts.public')
@section('title', 'P2P')
@section('content')

<div class="page-shell py-16"><h1 class="section-title">P2P Marketplace</h1>
<p class="section-sub">Escrow-protected peer trades with local payment methods. Do not trade outside Bitzlatoview.</p>
<div class="glass-card mt-8 overflow-x-auto p-4"><table class="data-table"><thead><tr><th>Side</th><th>Asset</th><th>Price</th><th>Limits</th><th>Fiat</th></tr></thead><tbody>
@foreach($ads as $ad)
<tr><td>{{ strtoupper($ad->side) }}</td><td>{{ $ad->asset->symbol ?? '-' }}</td><td class="font-mono">{{ number_format($ad->price,2) }}</td><td class="font-mono">{{ $ad->min_limit }} - {{ $ad->max_limit }}</td><td>{{ $ad->fiat_currency }}</td></tr>
@endforeach
</tbody></table></div>
<a href="{{ route('app.p2p.buy') }}" class="btn-brand mt-6 inline-flex">Trade in app</a></div>
@endsection
