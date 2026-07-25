<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\MarketPair;
use App\Models\Quote;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $usdt = Asset::where('symbol', 'USDT')->first();

        $pairs = [
            ['base' => 'BTC', 'price' => 64250.50, 'change' => 2.34, 'volume' => 850000000],
            ['base' => 'ETH', 'price' => 3120.75, 'change' => 1.85, 'volume' => 420000000],
            ['base' => 'SOL', 'price' => 148.20, 'change' => 6.42, 'volume' => 180000000],
            ['base' => 'XRP', 'price' => 0.5210, 'change' => -1.12, 'volume' => 95000000],
            ['base' => 'DOGE', 'price' => 0.1245, 'change' => 4.87, 'volume' => 60000000],
            ['base' => 'LTC', 'price' => 82.60, 'change' => -0.75, 'volume' => 32000000],
            ['base' => 'USDC', 'price' => 1.0001, 'change' => 0.01, 'volume' => 210000000],
        ];

        foreach ($pairs as $pair) {
            $base = Asset::where('symbol', $pair['base'])->first();

            $marketPair = MarketPair::updateOrCreate(
                ['symbol' => $pair['base'].'-USDT'],
                [
                    'base_asset_id' => $base->id,
                    'quote_asset_id' => $usdt->id,
                    'min_qty' => 0.0001,
                    'price_precision' => $pair['price'] < 1 ? 4 : 2,
                    'qty_precision' => 6,
                    'maker_fee_pct' => 0.1,
                    'taker_fee_pct' => 0.1,
                    'is_active' => true,
                ]
            );

            Quote::updateOrCreate(
                ['market_pair_id' => $marketPair->id],
                [
                    'price' => $pair['price'],
                    'change_24h_pct' => $pair['change'],
                    'high_24h' => $pair['price'] * 1.03,
                    'low_24h' => $pair['price'] * 0.97,
                    'volume_24h' => $pair['volume'],
                ]
            );
        }
    }
}
