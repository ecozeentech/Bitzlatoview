<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForexPair;
use App\Models\FuturesMarket;
use App\Models\StockInstrument;

class ExtendedMarketController extends Controller
{
    public function index()
    {
        $stocks = StockInstrument::all();
        $forexPairs = ForexPair::all();
        $futuresMarkets = FuturesMarket::with('asset')->get();

        return view('admin.markets-extended.index', compact('stocks', 'forexPairs', 'futuresMarkets'));
    }
}
