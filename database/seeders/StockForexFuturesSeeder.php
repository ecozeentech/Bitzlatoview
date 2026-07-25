<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\ForexPair;
use App\Models\FuturesMarket;
use App\Models\StockInstrument;
use Illuminate\Database\Seeder;

class StockForexFuturesSeeder extends Seeder
{
    public function run(): void
    {
        $stocks = [
            ['symbol' => 'AAPL', 'name' => 'Apple Inc.', 'last_price' => 214.32, 'change_pct' => 1.24],
            ['symbol' => 'MSFT', 'name' => 'Microsoft Corp.', 'last_price' => 441.58, 'change_pct' => 0.62],
            ['symbol' => 'TSLA', 'name' => 'Tesla Inc.', 'last_price' => 248.75, 'change_pct' => -2.15],
            ['symbol' => 'NVDA', 'name' => 'NVIDIA Corp.', 'last_price' => 128.90, 'change_pct' => 3.47],
            ['symbol' => 'AMZN', 'name' => 'Amazon.com Inc.', 'last_price' => 186.40, 'change_pct' => 0.95],
        ];

        foreach ($stocks as $stock) {
            StockInstrument::updateOrCreate(['symbol' => $stock['symbol']], $stock + ['exchange' => 'NASDAQ']);
        }

        $forexPairs = [
            ['symbol' => 'EUR/USD', 'base_currency' => 'EUR', 'quote_currency' => 'USD', 'bid' => 1.0842, 'ask' => 1.0844],
            ['symbol' => 'GBP/USD', 'base_currency' => 'GBP', 'quote_currency' => 'USD', 'bid' => 1.2705, 'ask' => 1.2708],
            ['symbol' => 'USD/JPY', 'base_currency' => 'USD', 'quote_currency' => 'JPY', 'bid' => 156.32, 'ask' => 156.35],
        ];

        foreach ($forexPairs as $pair) {
            ForexPair::updateOrCreate(['symbol' => $pair['symbol']], $pair + ['spread_pips' => 1.5]);
        }

        $btc = Asset::where('symbol', 'BTC')->first();
        $eth = Asset::where('symbol', 'ETH')->first();
        $sol = Asset::where('symbol', 'SOL')->first();

        $futuresMarkets = [
            ['symbol' => 'BTCUSDT-PERP', 'asset_id' => $btc->id, 'max_leverage' => 50, 'mark_price' => 64260, 'index_price' => 64255, 'funding_rate_pct' => 0.012],
            ['symbol' => 'ETHUSDT-PERP', 'asset_id' => $eth->id, 'max_leverage' => 50, 'mark_price' => 3122, 'index_price' => 3120, 'funding_rate_pct' => 0.009],
            ['symbol' => 'SOLUSDT-PERP', 'asset_id' => $sol->id, 'max_leverage' => 25, 'mark_price' => 148.4, 'index_price' => 148.2, 'funding_rate_pct' => 0.015],
        ];

        foreach ($futuresMarkets as $market) {
            FuturesMarket::updateOrCreate(['symbol' => $market['symbol']], $market + ['maintenance_margin_pct' => 0.5]);
        }
    }
}
