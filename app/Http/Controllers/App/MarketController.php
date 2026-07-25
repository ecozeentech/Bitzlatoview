<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\MarketPair;
use App\Models\WatchlistItem;
use Illuminate\Support\Facades\Auth;

class MarketController extends Controller
{
    public function index()
    {
        $markets = MarketPair::with(['baseAsset', 'quoteAsset', 'quote'])->where('is_active', true)->get();
        $watchlist = WatchlistItem::where('user_id', Auth::id())->pluck('market_pair_id')->all();

        return view('app.markets.index', compact('markets', 'watchlist'));
    }

    public function toggleWatchlist(MarketPair $marketPair)
    {
        $existing = WatchlistItem::where('user_id', Auth::id())->where('market_pair_id', $marketPair->id)->first();

        if ($existing) {
            $existing->delete();

            return back()->with('success', "Removed {$marketPair->symbol} from watchlist.");
        }

        WatchlistItem::create(['user_id' => Auth::id(), 'market_pair_id' => $marketPair->id]);

        return back()->with('success', "Added {$marketPair->symbol} to watchlist.");
    }
}
