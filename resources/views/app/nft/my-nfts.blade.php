@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">My NFTs</h1>
    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @forelse ($items as $item)
            <a href="{{ route('app.nft.items.show', $item) }}" class="glass-card block p-4 hover:border-brand/40">
                <div class="mb-2 h-32 rounded-lg bg-gradient-to-br from-purple/20 to-info/20"></div>
                <p class="font-medium">{{ $item->name }}</p>
                <p class="text-xs text-text-muted">{{ $item->collection->name }}</p>
            </a>
        @empty
            <p class="text-sm text-text-muted">You don't own any NFTs yet.</p>
        @endforelse
    </div>
</div>
@endsection
