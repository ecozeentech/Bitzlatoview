<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\MarketPair;
use Illuminate\Support\Facades\Cache;

/**
 * Simulated pricing service. Resolves a rough USD value for any seeded asset using the
 * `market_pairs`/`quotes` tables (asset-USDT pairs). Stablecoins and USD are treated as 1:1.
 *
 * This is intentionally simple for the MVP simulation; a real deployment would replace this
 * with a licensed market data provider.
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
