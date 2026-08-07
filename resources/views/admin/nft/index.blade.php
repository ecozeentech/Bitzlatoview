@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ showCreate: false, manage: null }">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">NFT Collections</h1>
        <button type="button" @click="showCreate = !showCreate" class="btn-brand text-sm">+ New Collection</button>
    </div>

    <p class="text-xs text-text-muted">Purchases, listings and bids settle on Bitzlatoview's internal ledger — items are not yet minted on-chain. Make sure listings reflect this.</p>

    <div x-show="showCreate" x-cloak class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Create collection</h2>
        <form method="POST" action="{{ route('admin.nft.collections.store') }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-3">
            @csrf
            <div>
                <label class="label-field">Name</label>
                <input type="text" name="name" class="input-field" required>
            </div>
            <div class="md:col-span-2">
                <label class="label-field">Banner image</label>
                <input type="file" name="banner_image" accept="image/*" class="input-field">
            </div>
            <div class="md:col-span-3">
                <label class="label-field">Description</label>
                <textarea name="description" class="input-field" rows="2"></textarea>
            </div>
            <div class="md:col-span-3">
                <button class="btn-brand text-sm">Create Collection</button>
            </div>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Floor</th><th>Volume</th><th>Owners</th><th>Items</th><th></th></tr></thead>
            <tbody>
                @forelse ($collections as $c)
                    <tr>
                        <td class="flex items-center gap-2">
                            @if ($c->banner_image)
                                <img src="{{ asset('storage/'.$c->banner_image) }}" class="h-8 w-8 rounded object-cover">
                            @endif
                            {{ $c->name }}
                        </td>
                        <td class="font-numeric">{{ $c->floor_price }} ETH</td>
                        <td class="font-numeric">{{ number_format($c->volume) }}</td>
                        <td class="font-numeric">{{ number_format($c->owners_count) }}</td>
                        <td class="font-numeric">{{ $c->items_count }}</td>
                        <td class="flex gap-2">
                            <button type="button" @click="manage = manage === {{ $c->id }} ? null : {{ $c->id }}" class="text-xs text-brand hover:underline">Manage</button>
                            @if ($c->items_count === 0)
                                <form method="POST" action="{{ route('admin.nft.collections.destroy', $c) }}" onsubmit="return confirm('Delete this collection?')">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                            @endif
                        </td>
                    </tr>
                    <tr x-show="manage === {{ $c->id }}" x-cloak>
                        <td colspan="6" class="bg-surface-2/40 p-4 space-y-4">
                            <form method="POST" action="{{ route('admin.nft.collections.update', $c) }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-4">
                                @csrf @method('PUT')
                                <input type="text" name="name" class="input-field" value="{{ $c->name }}" required>
                                <input type="number" step="0.0001" name="floor_price" class="input-field" value="{{ $c->floor_price }}" placeholder="Floor price">
                                <input type="file" name="banner_image" accept="image/*" class="input-field">
                                <textarea name="description" class="input-field md:col-span-4" rows="2">{{ $c->description }}</textarea>
                                <button class="btn-brand text-xs md:col-span-4 w-fit">Save Collection</button>
                            </form>

                            <div>
                                <h3 class="mb-2 text-sm font-semibold">Items in {{ $c->name }}</h3>
                                <table class="data-table">
                                    <thead><tr><th>Item</th><th>Token ID</th><th>Owner</th><th>Price</th><th>Listed</th><th></th></tr></thead>
                                    <tbody>
                                        @foreach ($c->items as $item)
                                            <tr>
                                                <td class="flex items-center gap-2">
                                                    @if ($item->image)<img src="{{ asset('storage/'.$item->image) }}" class="h-6 w-6 rounded object-cover">@endif
                                                    {{ $item->name }}
                                                </td>
                                                <td class="text-text-muted">{{ $item->token_id }}</td>
                                                <td class="text-text-muted">{{ $item->owner?->name ?? 'Unowned (marketplace)' }}</td>
                                                <td class="font-numeric">{{ $item->price ? number_format($item->price, 4) : '—' }}</td>
                                                <td>{{ $item->is_listed ? 'Yes' : 'No' }}</td>
                                                <td>
                                                    <form method="POST" action="{{ route('admin.nft.items.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <form method="POST" action="{{ route('admin.nft.items.store', $c) }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-5">
                                @csrf
                                <input type="text" name="name" class="input-field" placeholder="Item name" required>
                                <input type="text" name="token_id" class="input-field" placeholder="Token ID" required>
                                <input type="number" step="0.0001" name="price" class="input-field" placeholder="Price (ETH)">
                                <input type="file" name="image" accept="image/*" class="input-field">
                                <button class="btn-brand text-xs">+ Add Item</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No collections yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
