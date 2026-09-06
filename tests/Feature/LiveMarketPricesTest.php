<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\FuturesMarket;
use App\Models\MarketPair;
use App\Services\MarketDataService;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Every market on the platform (spot header, market tables, dashboard, homepage, futures)
 * ultimately reads from the `quotes` table kept fresh by MarketDataService, and the frontend
 * polls the JSON ticker below to reflect that without a full page reload — see
 * resources/js/app.js's initLivePrices() and MarketController::prices().
 */
class LiveMarketPricesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_prices_endpoint_returns_current_quotes_for_every_active_market(): void
    {
        $this->seed();

        $response = $this->getJson(route('markets.prices'));

        $response->assertOk();

        $btc = MarketPair::where('symbol', 'BTC-USDT')->with('quote')->firstOrFail();

        $response->assertJsonPath('BTC-USDT.price', (float) $btc->quote->price);
        $response->assertJsonPath('BTC-USDT.change_24h_pct', (float) $btc->quote->change_24h_pct);
        $response->assertJsonStructure([
            'BTC-USDT' => ['price', 'change_24h_pct', 'high_24h', 'low_24h', 'volume_24h', 'updated_at'],
        ]);
    }

    public function test_prices_endpoint_is_public_and_does_not_require_authentication(): void
    {
        $this->seed();

        $this->getJson(route('markets.prices'))->assertOk();
    }

    public function test_syncing_market_prices_updates_the_quote_and_the_matching_futures_mark_price(): void
    {
        $this->seed();

        Http::fake([
            'https://api.coingecko.com/api/v3/coins/markets*' => Http::response([
                ['id' => 'bitcoin', 'current_price' => 91000.5, 'price_change_percentage_24h' => 3.5, 'high_24h' => 92000, 'low_24h' => 89000, 'total_volume' => 999000000],
                ['id' => 'ethereum', 'current_price' => 4500.25, 'price_change_percentage_24h' => -2.1, 'high_24h' => 4600, 'low_24h' => 4400, 'total_volume' => 500000000],
                ['id' => 'solana', 'current_price' => 210.10, 'price_change_percentage_24h' => 1.1, 'high_24h' => 215, 'low_24h' => 205, 'total_volume' => 100000000],
                ['id' => 'ripple', 'current_price' => 0.75, 'price_change_percentage_24h' => 0.5, 'high_24h' => 0.8, 'low_24h' => 0.7, 'total_volume' => 90000000],
                ['id' => 'dogecoin', 'current_price' => 0.2, 'price_change_percentage_24h' => 0.2, 'high_24h' => 0.21, 'low_24h' => 0.19, 'total_volume' => 60000000],
                ['id' => 'litecoin', 'current_price' => 100.5, 'price_change_percentage_24h' => -0.5, 'high_24h' => 101, 'low_24h' => 99, 'total_volume' => 30000000],
                ['id' => 'usd-coin', 'current_price' => 1.0, 'price_change_percentage_24h' => 0.0, 'high_24h' => 1.0, 'low_24h' => 1.0, 'total_volume' => 200000000],
            ]),
        ]);

        $btcAsset = Asset::where('symbol', 'BTC')->firstOrFail();
        Cache::put('price:BTC', 1.0, 30);
        FuturesMarket::where('asset_id', $btcAsset->id)->update(['mark_price' => 1, 'index_price' => 1]);

        $updated = app(MarketDataService::class)->syncCryptoQuotes();

        $this->assertSame(7, $updated);

        $btcPair = MarketPair::where('symbol', 'BTC-USDT')->with('quote')->firstOrFail();
        $this->assertSame('91000.5', (string) $btcPair->quote->price);

        // The 30s PricingService cache must be invalidated immediately on sync, not left to
        // expire on its own — otherwise fees/orders/futures could use a stale cached price for
        // up to 30 more seconds after a successful sync.
        $this->assertNull(Cache::get('price:BTC'));
        $this->assertEqualsWithDelta(91000.5, app(PricingService::class)->usdPrice($btcAsset), 0.0001);

        $btcFutures = FuturesMarket::where('asset_id', $btcAsset->id)->first();
        $this->assertEqualsWithDelta(91000.5, (float) $btcFutures->mark_price, 0.0001);
        $this->assertEqualsWithDelta(91000.5, (float) $btcFutures->index_price, 0.0001);
    }
}
