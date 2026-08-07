<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'symbol' => ['required', 'string', 'max:15', 'unique:assets,symbol'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:crypto,fiat,stock'],
            'decimals' => ['required', 'integer', 'min:0', 'max:18'],
        ]);
        $data['symbol'] = strtoupper($data['symbol']);

        $asset = Asset::create($data);
        AuditLog::record(auth()->user(), 'asset.created', Asset::class, $asset->id);

        return back()->with('success', "Asset {$asset->symbol} created.");
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $asset->update($data);
        AuditLog::record(auth()->user(), 'asset.updated', Asset::class, $asset->id);

        return back()->with('success', "Asset {$asset->symbol} updated.");
    }
}
