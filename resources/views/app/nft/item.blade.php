@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="glass-card p-6">
        <div class="mb-4 h-56 rounded-lg bg-gradient-to-br from-brand/20 via-purple/20 to-info/20"></div>
        <h1 class="text-xl font-bold">{{ $item->name }}</h1>
        <p class="text-sm text-text-muted">Collection: {{ $item->collection->name }} · Token #{{ $item->token_id }}</p>
        <p class="mt-2 font-numeric text-2xl font-bold text-brand">{{ $item->price ?? '—' }} ETH</p>
        <p class="text-xs text-text-muted">{{ $item->owner ? 'Owned by '.$item->owner->name : 'Unowned (platform inventory)' }}</p>

        <div class="mt-4 flex gap-2">
            @if ($item->is_listed && $item->owner_user_id !== auth()->id())
                <form method="POST" action="{{ route('app.nft.items.buy', $item) }}">@csrf<button class="btn-brand text-sm">Buy Now</button></form>
            @endif
            @if ($item->owner_user_id === auth()->id() && ! $item->is_listed)
                <form method="POST" action="{{ route('app.nft.items.list', $item) }}" class="flex gap-2">
                    @csrf
                    <input type="number" step="0.001" name="price" class="input-field w-32" placeholder="Price ETH" required>
                    <button class="btn-outline text-sm">List for Sale</button>
                </form>
            @endif
            @if ($item->owner_user_id !== auth()->id())
                <form method="POST" action="{{ route('app.nft.items.bid', $item) }}" class="flex gap-2">
                    @csrf
                    <input type="number" step="0.001" name="amount" class="input-field w-32" placeholder="Bid amount" required>
                    <button class="btn-outline text-sm">Place Bid</button>
                </form>
            @endif
        </div>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Bids</h2>
        @forelse ($item->bids as $bid)
            <div class="flex justify-between border-b border-border/60 py-2 text-sm"><span>{{ $bid->bidder->name }}</span><span class="font-numeric">{{ $bid->amount }} ETH</span></div>
        @empty
            <p class="text-sm text-text-muted">No bids yet.</p>
        @endforelse
    </div>
</div>
@endsection
