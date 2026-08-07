<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\MarketPair;
use App\Models\Quote;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function index()
    {
        $pairs = MarketPair::with('baseAsset', 'quoteAsset', 'quote')->get();
        $assets = Asset::orderBy('symbol')->get();

        return view('admin.markets.index', compact('pairs', 'assets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'base_asset_id' => ['required', 'exists:assets,id'],
            'quote_asset_id' => ['required', 'exists:assets,id', 'different:base_asset_id'],
            'maker_fee_pct' => ['required', 'numeric', 'min:0', 'max:5'],
            'taker_fee_pct' => ['required', 'numeric', 'min:0', 'max:5'],
            'initial_price' => ['required', 'numeric', 'min:0'],
        ]);

        $base = Asset::findOrFail($data['base_asset_id']);
        $quoteAsset = Asset::findOrFail($data['quote_asset_id']);
        $symbol = "{$base->symbol}-{$quoteAsset->symbol}";

        if (MarketPair::where('symbol', $symbol)->exists()) {
            return back()->with('error', "Pair {$symbol} already exists.");
        }

        $pair = MarketPair::create([
            'symbol' => $symbol,
            'base_asset_id' => $base->id,
            'quote_asset_id' => $quoteAsset->id,
            'maker_fee_pct' => $data['maker_fee_pct'],
            'taker_fee_pct' => $data['taker_fee_pct'],
            'is_active' => true,
        ]);

        Quote::create(['market_pair_id' => $pair->id, 'price' => $data['initial_price']]);

        AuditLog::record(auth()->user(), 'market.created', MarketPair::class, $pair->id);

        return back()->with('success', "Trading pair {$symbol} created.");
    }

    public function update(Request $request, MarketPair $pair)
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
            'maker_fee_pct' => ['required', 'numeric', 'min:0', 'max:5'],
            'taker_fee_pct' => ['required', 'numeric', 'min:0', 'max:5'],
        ]);

        $pair->update($data);

        if ($request->filled('price')) {
            $pair->quote()->update([
                'price' => $request->input('price'),
                'change_24h_pct' => $request->input('change_24h_pct', $pair->quote->change_24h_pct ?? 0),
            ]);
        }

        AuditLog::record(auth()->user(), 'market.updated', MarketPair::class, $pair->id);

        return back()->with('success', 'Market updated.');
    }
}
