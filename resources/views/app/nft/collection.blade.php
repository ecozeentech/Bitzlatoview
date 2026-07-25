@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">{{ $collection->name }}</h1>
    <p class="text-sm text-text-muted">{{ $collection->description }}</p>

    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @foreach ($collection->items as $item)
            <a href="{{ route('app.nft.items.show', $item) }}" class="glass-card block p-4 hover:border-brand/40">
                <div class="mb-2 h-32 rounded-lg bg-gradient-to-br from-purple/20 to-info/20"></div>
                <p class="font-medium">{{ $item->name }}</p>
                <p class="font-numeric text-sm text-brand">{{ $item->price }} ETH</p>
                @if ($item->owner)<p class="text-xs text-text-muted">Owned by {{ $item->owner->name }}</p>@endif
            </a>
        @endforeach
    </div>
</div>
@endsection
