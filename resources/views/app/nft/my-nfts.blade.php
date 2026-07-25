@extends('layouts.app-shell')
@section('title', 'My NFTs')
@section('content')

<h1 class="text-2xl font-bold mb-6">My NFTs</h1>
@forelse($items as $item)<div class="glass-card mb-3 p-4">{{ $item->name }} · {{ $item->nftCollection->name ?? '' }}</div>@empty<p class="text-muted">No NFTs yet. Connect WalletConnect for external holdings (not custodial).</p>@endforelse

@endsection
