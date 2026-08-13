<?php

namespace Database\Seeders;

use App\Models\SignalPackage;
use Illuminate\Database\Seeder;

class SignalSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Steady BTC Signal',
                'description' => 'Tracks Bitcoin price movement with conservative exposure. Expected return is a disclosed estimate based on historical performance, not a guarantee.',
                'risk_level' => 'low',
                'min_investment' => 50,
                'max_investment' => 5000,
                'expected_return_pct' => 6,
                'duration_days' => 30,
                'fee_pct' => 1,
                'tracked_asset_symbol' => 'BTC',
                'status' => 'active',
            ],
            [
                'name' => 'ETH Momentum Signal',
                'description' => 'Tracks Ethereum price movement with moderate exposure for users comfortable with more volatility.',
                'risk_level' => 'moderate',
                'min_investment' => 100,
                'max_investment' => 10000,
                'expected_return_pct' => 12,
                'duration_days' => 45,
                'fee_pct' => 1.5,
                'tracked_asset_symbol' => 'ETH',
                'status' => 'active',
            ],
            [
                'name' => 'High-Volatility Alt Signal',
                'description' => 'Higher-risk signal tracking a volatile altcoin. Suitable only for users who understand they may lose their full allocation.',
                'risk_level' => 'high',
                'min_investment' => 250,
                'max_investment' => null,
                'expected_return_pct' => 25,
                'duration_days' => 60,
                'fee_pct' => 2,
                'tracked_asset_symbol' => 'SOL',
                'status' => 'active',
            ],
        ];

        foreach ($packages as $package) {
            SignalPackage::updateOrCreate(['name' => $package['name']], $package);
        }
    }
}
