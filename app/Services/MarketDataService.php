<?php

namespace App\Services;

use App\Models\FuturesMarket;
use App\Models\MarketPair;
use App\Models\Quote;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches real, live market prices from CoinGecko's public API and writes them into the
 * `quotes` table that the rest of the app reads from (spot trading, swap, buy/sell, AI bots,
 * copy trading, futures). The initial seeded prices in MarketSeeder are only a fallback until
 * this runs for the first time.
 *
 * CoinGecko's free public API has no key requirement for this endpoint, but is rate-limited.
 * For a production deployment with meaningful traffic, set COINGECKO_API_KEY in .env (a paid
 * CoinGecko Pro plan) to use the pro endpoint instead — see config/services.php.
 */
class MarketDataService
{
    /** Maps our internal asset symbol to CoinGecko's coin id. */
    protected const COINGECKO_IDS = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'USDT' => 'tether',
        'USDC' => 'usd-coin',
        'SOL' => 'solana',
        'XRP' => 'ripple',
        'DOGE' => 'dogecoin',
        'LTC' => 'litecoin',
    ];

    public function syncCryptoQuotes(): int
    {
        $pairs = MarketPair::with('baseAsset')->where('is_active', true)->get()
            ->filter(fn (MarketPair $pair) => isset(self::COINGECKO_IDS[$pair->baseAsset->symbol]));

        if ($pairs->isEmpty()) {
            return 0;
        }

        $idsBySymbol = $pairs->mapWithKeys(fn (MarketPair $pair) => [
            $pair->baseAsset->symbol => self::COINGECKO_IDS[$pair->baseAsset->symbol],
        ]);

        $data = $this->fetchMarkets($idsBySymbol->unique()->values()->all());

        if ($data === null) {
            Log::warning('MarketDataService: CoinGecko fetch failed, keeping last known quotes.');

            return 0;
        }

        $byId = collect($data)->keyBy('id');
        $updated = 0;

        foreach ($pairs as $pair) {
            $coinId = $idsBySymbol[$pair->baseAsset->symbol];
            $row = $byId->get($coinId);

            if (! $row || ! isset($row['current_price'])) {
                continue;
            }

            Quote::updateOrCreate(
                ['market_pair_id' => $pair->id],
                [
                    'price' => $row['current_price'],
                    'change_24h_pct' => $row['price_change_percentage_24h'] ?? 0,
                    'high_24h' => $row['high_24h'] ?? $row['current_price'],
                    'low_24h' => $row['low_24h'] ?? $row['current_price'],
                    'volume_24h' => $row['total_volume'] ?? 0,
                ]
            );

            // PricingService caches usdPrice() for 30s independently of this sync's cadence —
            // forget it now so every fee/order/swap/futures calculation immediately sees the
            // fresh price instead of serving a stale cached value for up to 30 more seconds.
            Cache::forget("price:{$pair->baseAsset->symbol}");

            $this->syncFuturesMarkPrice($pair->baseAsset->id, (float) $row['current_price']);

            $updated++;
        }

        return $updated;
    }

    /**
     * Futures markets display their own `mark_price`/`index_price` (see the Futures Trading
     * page) which otherwise only moves when a user opens/closes a position on that market —
     * keep it tracking the underlying asset's live spot price so the futures list never looks
     * stale between trades, same as every other market on the platform.
     */
    protected function syncFuturesMarkPrice(int $assetId, float $price): void
    {
        FuturesMarket::where('asset_id', $assetId)->update([
            'mark_price' => $price,
            'index_price' => $price,
        ]);
    }

    /**
     * @param  array<int, string>  $coinIds
     * @return array<int, array<string, mixed>>|null
     */
    protected function fetchMarkets(array $coinIds): ?array
    {
        if (empty($coinIds)) {
            return [];
        }

        $apiKey = config('services.coingecko.key');
        $baseUrl = $apiKey ? 'https://pro-api.coingecko.com/api/v3' : 'https://api.coingecko.com/api/v3';

        try {
            $response = Http::timeout(10)
                ->when($apiKey, fn ($request) => $request->withHeaders(['x-cg-pro-api-key' => $apiKey]))
                ->get("{$baseUrl}/coins/markets", [
                    'vs_currency' => 'usd',
                    'ids' => implode(',', $coinIds),
                    'order' => 'market_cap_desc',
                    'price_change_percentage' => '24h',
                ]);

            if (! $response->successful()) {
                Log::warning('MarketDataService: CoinGecko responded with '.$response->status());

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('MarketDataService: CoinGecko request failed — '.$e->getMessage());

            return null;
        }
    }
}
