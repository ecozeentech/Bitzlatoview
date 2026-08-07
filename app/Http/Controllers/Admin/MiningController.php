<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\MiningPackage;
use Illuminate\Http\Request;

class MiningController extends Controller
{
    public function index()
    {
        $packages = MiningPackage::withCount('contracts')->with('asset')->get();
        $assets = Asset::where('type', 'crypto')->get();

        return view('admin.mining.index', compact('packages', 'assets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'asset_id' => ['required', 'exists:assets,id'],
            'hashrate_th' => ['required', 'numeric', 'gt:0'],
            'term_days' => ['required', 'integer', 'gt:0'],
            'maintenance_fee_pct' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'gt:0'],
            'estimated_daily_reward_pct' => ['required', 'numeric', 'min:0'],
        ]);

        MiningPackage::create($data + [
            'risk_disclosure' => 'Mining rewards follow the disclosed reward rate and are not guaranteed. Rewards depend on network difficulty, coin price, and maintenance fees, and can go to zero.',
            'is_published' => true,
        ]);

        return back()->with('success', 'Mining package created.');
    }

    public function update(Request $request, MiningPackage $package)
    {
        $data = $request->validate(['is_published' => ['required', 'boolean']]);
        $package->update($data);

        return back()->with('success', 'Package updated.');
    }
}
