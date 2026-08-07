<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\InvestmentProduct;
use Illuminate\Database\Seeder;

class InvestmentSeeder extends Seeder
{
    public function run(): void
    {
        $usdt = Asset::where('symbol', 'USDT')->first();
        $btc = Asset::where('symbol', 'BTC')->first();
        $eth = Asset::where('symbol', 'ETH')->first();

        $products = [
            ['name' => 'Flexible USDT Earn', 'asset_id' => $usdt->id, 'apy_pct' => 4.5, 'lock_days' => 0, 'min_amount' => 10],
            ['name' => '30-Day Locked USDT', 'asset_id' => $usdt->id, 'apy_pct' => 7.2, 'lock_days' => 30, 'min_amount' => 100],
            ['name' => '90-Day Locked BTC', 'asset_id' => $btc->id, 'apy_pct' => 3.8, 'lock_days' => 90, 'min_amount' => 0.01],
            ['name' => 'ETH Staking-Style Product', 'asset_id' => $eth->id, 'apy_pct' => 5.5, 'lock_days' => 60, 'min_amount' => 0.1],
        ];

        foreach ($products as $product) {
            InvestmentProduct::updateOrCreate(
                ['name' => $product['name']],
                $product + [
                    'description' => 'Rates are illustrative and not guaranteed. Investment involves risk of loss.',
                    'status' => 'active',
                ]
            );
        }
    }
}
