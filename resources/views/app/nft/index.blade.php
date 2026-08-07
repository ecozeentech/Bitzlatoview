@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">NFT Marketplace</h1>
        <div class="flex gap-2">
            <a href="{{ url('/app/nft/my-nfts') }}" class="btn-outline text-sm">My NFTs</a>
            <a href="{{ url('/app/settings/wallet-connect') }}" class="btn-brand text-sm">Connect Wallet</a>
        </div>
    </div>
    <div class="risk-banner">NFTs are speculative and illiquid assets. Trades here settle on Bitzlatoview's internal ledger — no real on-chain trading until smart contracts/providers are connected.</div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($collections as $c)
            <a href="{{ route('app.nft.collections.show', $c) }}" class="glass-card block p-5 hover:border-brand/40">
                <div class="mb-3 h-24 rounded-lg bg-gradient-to-br from-brand/20 to-purple/20"></div>
                <h2 class="font-semibold">{{ $c->name }}</h2>
                <div class="mt-2 grid grid-cols-3 gap-2 text-xs text-text-muted">
                    <div>Floor<br><span class="font-numeric text-text-main">{{ $c->floor_price }} ETH</span></div>
                    <div>Volume<br><span class="font-numeric text-text-main">{{ number_format($c->volume) }}</span></div>
                    <div>Owners<br><span class="font-numeric text-text-main">{{ number_format($c->owners_count) }}</span></div>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
