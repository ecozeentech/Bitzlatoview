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
