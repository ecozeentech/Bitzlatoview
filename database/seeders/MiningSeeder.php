<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\MiningPackage;
use Illuminate\Database\Seeder;

class MiningSeeder extends Seeder
{
    public function run(): void
    {
        $btc = Asset::where('symbol', 'BTC')->first();
        $eth = Asset::where('symbol', 'ETH')->first();
        $ltc = Asset::where('symbol', 'LTC')->first();

        $packages = [
            ['name' => 'BTC Starter Hashpower', 'asset_id' => $btc->id, 'hashrate_th' => 10, 'term_days' => 90, 'maintenance_fee_pct' => 3, 'price' => 500, 'estimated_daily_reward_pct' => 0.09],
            ['name' => 'BTC Pro Hashpower', 'asset_id' => $btc->id, 'hashrate_th' => 50, 'term_days' => 180, 'maintenance_fee_pct' => 2.5, 'price' => 2200, 'estimated_daily_reward_pct' => 0.1],
            ['name' => 'ETH Validator Pool Share', 'asset_id' => $eth->id, 'hashrate_th' => 5, 'term_days' => 120, 'maintenance_fee_pct' => 2, 'price' => 800, 'estimated_daily_reward_pct' => 0.08],
            ['name' => 'LTC Scrypt Miner Lease', 'asset_id' => $ltc->id, 'hashrate_th' => 20, 'term_days' => 60, 'maintenance_fee_pct' => 3.5, 'price' => 350, 'estimated_daily_reward_pct' => 0.11],
        ];

        foreach ($packages as $package) {
            MiningPackage::updateOrCreate(
                ['name' => $package['name']],
                $package + [
                    'risk_disclosure' => 'Mining rewards are simulated and not guaranteed. Rewards depend on network difficulty, coin price, and maintenance fees, and can go to zero.',
                    'is_published' => true,
                ]
            );
        }
    }
}
