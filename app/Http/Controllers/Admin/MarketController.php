<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MarketPair;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function index()
    {
        $pairs = MarketPair::with('baseAsset', 'quoteAsset', 'quote')->get();

        return view('admin.markets.index', compact('pairs'));
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
