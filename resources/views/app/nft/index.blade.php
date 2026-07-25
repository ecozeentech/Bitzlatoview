@extends('layouts.app-shell')
@section('title', 'NFT')
@section('content')

<h1 class="text-2xl font-bold mb-6">NFT Marketplace</h1>
<div class="mb-4 flex gap-2"><a href="{{ route('app.nft.collections') }}" class="btn-outline text-xs">Collections</a><a href="{{ route('app.nft.my') }}" class="btn-outline text-xs">My NFTs</a></div>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
@foreach($collections as $c)
<div class="glass-card p-5"><div class="mb-3 h-28 rounded-xl bg-gradient-to-br from-brand/20 to-purple/20"></div><h3 class="font-semibold">{{ $c->name }}</h3><p class="text-sm text-muted mt-1">Floor {{ $c->floor_price }} · Vol {{ $c->volume_24h }}</p></div>
@endforeach
</div>

@endsection
