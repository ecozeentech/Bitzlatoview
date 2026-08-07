<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FuturesMarket;
use Illuminate\Http\Request;

class FuturesMarketController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'symbol' => ['required', 'string', 'max:30', 'unique:futures_markets,symbol'],
            'asset_id' => ['required', 'exists:assets,id'],
            'max_leverage' => ['required', 'integer', 'min:1', 'max:200'],
            'maintenance_margin_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'funding_rate_pct' => ['required', 'numeric'],
        ]);
        $data['symbol'] = strtoupper($data['symbol']);

        $market = FuturesMarket::create($data);
        AuditLog::record(auth()->user(), 'futures_market.created', FuturesMarket::class, $market->id);

        return back()->with('success', "Futures market {$market->symbol} added.");
    }

    public function update(Request $request, FuturesMarket $market)
    {
        $data = $request->validate([
            'max_leverage' => ['required', 'integer', 'min:1', 'max:200'],
            'maintenance_margin_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'funding_rate_pct' => ['required', 'numeric'],
        ]);

        $market->update($data);
        AuditLog::record(auth()->user(), 'futures_market.updated', FuturesMarket::class, $market->id);

        return back()->with('success', "Futures market {$market->symbol} updated.");
    }

    public function destroy(FuturesMarket $market)
    {
        if ($market->positions()->where('status', 'open')->exists()) {
            return back()->with('error', 'Cannot delete a market with open user positions.');
        }

        AuditLog::record(auth()->user(), 'futures_market.deleted', FuturesMarket::class, $market->id);
        $market->delete();

        return back()->with('success', 'Futures market deleted.');
    }
}
