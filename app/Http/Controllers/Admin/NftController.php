<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\NftCollection;
use App\Models\NftItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NftController extends Controller
{
    public function index()
    {
        $collections = NftCollection::withCount('items')->with('items.owner')->latest()->get();

        return view('admin.nft.index', compact('collections'));
    }

    public function storeCollection(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'banner_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);

        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $request->file('banner_image')->store('nft', 'public');
        }

        $collection = NftCollection::create($data);

        AuditLog::record(auth()->user(), 'nft_collection.created', NftCollection::class, $collection->id);

        return back()->with('success', "Collection \"{$collection->name}\" created.");
    }

    public function updateCollection(Request $request, NftCollection $collection)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'floor_price' => ['required', 'numeric', 'min:0'],
            'banner_image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('banner_image')) {
            if ($collection->banner_image) {
                Storage::disk('public')->delete($collection->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('nft', 'public');
        }

        $collection->update($data);

        AuditLog::record(auth()->user(), 'nft_collection.updated', NftCollection::class, $collection->id);

        return back()->with('success', "Collection \"{$collection->name}\" updated.");
    }

    public function destroyCollection(NftCollection $collection)
    {
        if ($collection->items()->exists()) {
            return back()->with('error', 'Cannot delete a collection that still has items — remove the items first.');
        }

        AuditLog::record(auth()->user(), 'nft_collection.deleted', NftCollection::class, $collection->id);
        $collection->delete();

        return back()->with('success', 'Collection deleted.');
    }

    public function storeItem(Request $request, NftCollection $collection)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'token_id' => ['required', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $data['nft_collection_id'] = $collection->id;
        $data['is_listed'] = ! empty($data['owner_user_id']) ? false : true;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('nft', 'public');
        }

        $item = NftItem::create($data);
        $collection->increment('items_count');
        if ($item->owner_user_id) {
            $collection->increment('owners_count');
        }

        AuditLog::record(auth()->user(), 'nft_item.created', NftItem::class, $item->id);

        return back()->with('success', "Item \"{$item->name}\" added to {$collection->name}.");
    }

    public function updateItem(Request $request, NftItem $item)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_listed' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
        $data['is_listed'] = $request->boolean('is_listed');

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('nft', 'public');
        }

        $item->update($data);

        AuditLog::record(auth()->user(), 'nft_item.updated', NftItem::class, $item->id);

        return back()->with('success', "Item \"{$item->name}\" updated.");
    }

    public function destroyItem(NftItem $item)
    {
        $collection = $item->collection;
        AuditLog::record(auth()->user(), 'nft_item.deleted', NftItem::class, $item->id);
        $item->delete();
        $collection->decrement('items_count');

        return back()->with('success', 'Item deleted.');
    }
}
