<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\MarketPair;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves a USD value for any seeded asset using the `market_pairs`/`quotes` tables, which
 * are kept fresh with real prices by App\Services\MarketDataService (CoinGecko). Stablecoins
 * and USD are treated as 1:1. Stocks/forex are not yet fed by a licensed market data vendor
 * and remain in paper-trading mode — see App\Http\Controllers\App\StockController/ForexController.
 */
class PricingService
{
    protected const STABLE = ['USDT', 'USDC', 'USD'];

    public function usdPrice(Asset $asset): float
    {
        if (in_array($asset->symbol, self::STABLE, true)) {
            return 1.0;
        }

        return Cache::remember("price:{$asset->symbol}", 30, function () use ($asset) {
            $pair = MarketPair::where('base_asset_id', $asset->id)->with('quote')->first();

            return (float) ($pair?->quote?->price ?? 0);
        });
    }

    public function convert(Asset $from, Asset $to, float $amount): float
    {
        $fromUsd = $this->usdPrice($from);
        $toUsd = $this->usdPrice($to) ?: 1;

        return $toUsd > 0 ? ($amount * $fromUsd) / $toUsd : 0;
    }
}
