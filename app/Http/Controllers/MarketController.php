<?php

namespace App\Http\Controllers;

use App\Models\MarketPair;
use App\Models\NftCollection;
use App\Models\StockInstrument;

class MarketController extends Controller
{
    public function index()
    {
        $markets = MarketPair::with(['baseAsset', 'quoteAsset', 'quote'])->get();

        return view('public.markets.index', ['markets' => $markets]);
    }

    /**
     * Lightweight JSON ticker polled by the frontend (see resources/js/app.js) to keep every
     * price shown on the site — spot header, market tables, dashboard, homepage ticker — in
     * sync with the latest quote without a full page reload. Public: prices aren't sensitive
     * and unauthenticated visitors see live prices on the public markets/homepage too.
     */
    public function prices()
    {
        $markets = MarketPair::where('is_active', true)->with('quote')->get();

        return response()->json(
            $markets->mapWithKeys(function (MarketPair $market) {
                $quote = $market->quote;

                return [$market->symbol => [
                    'price' => (float) ($quote->price ?? 0),
                    'change_24h_pct' => (float) ($quote->change_24h_pct ?? 0),
                    'high_24h' => (float) ($quote->high_24h ?? 0),
                    'low_24h' => (float) ($quote->low_24h ?? 0),
                    'volume_24h' => (float) ($quote->volume_24h ?? 0),
                    'updated_at' => $quote?->updated_at?->toIso8601String(),
                ]];
            })
        );
    }

    public function topGainers()
    {
        $markets = MarketPair::with(['baseAsset', 'quote'])->get()
            ->sortByDesc(fn ($m) => $m->quote?->change_24h_pct)->values();

        $stocks = StockInstrument::orderByDesc('change_pct')->take(5)->get();
        $collections = NftCollection::orderByDesc('volume')->take(5)->get();

        return view('public.markets.top-gainers', compact('markets', 'stocks', 'collections'));
    }

    public function topLosers()
    {
        $markets = MarketPair::with(['baseAsset', 'quote'])->get()
            ->sortBy(fn ($m) => $m->quote?->change_24h_pct)->values();

        $stocks = StockInstrument::orderBy('change_pct')->take(5)->get();

        return view('public.markets.top-losers', compact('markets', 'stocks'));
    }

    public function newListings()
    {
        $markets = MarketPair::with(['baseAsset', 'quote'])->latest()->get();

        return view('public.markets.new-listings', compact('markets'));
    }
}
