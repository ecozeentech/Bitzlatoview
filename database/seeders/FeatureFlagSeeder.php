<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            ['key' => 'futures_trading', 'name' => 'Futures Trading', 'description' => 'Enable high-risk leveraged futures trading module.'],
            ['key' => 'stocks_trading', 'name' => 'Stock Trading', 'description' => 'Enable paper stock trading module.'],
            ['key' => 'forex_trading', 'name' => 'Forex Trading', 'description' => 'Enable paper forex trading module.'],
            ['key' => 'ai_bots', 'name' => 'AI Trading Bots', 'description' => 'Enable AI bot investment marketplace.'],
            ['key' => 'mining', 'name' => 'Crypto Mining', 'description' => 'Enable mining contract purchases.'],
            ['key' => 'virtual_cards', 'name' => 'Virtual Cards', 'description' => 'Enable virtual card issuance (requires a licensed card-issuing provider for real cards).'],
            ['key' => 'nft_marketplace', 'name' => 'NFT Marketplace', 'description' => 'Enable NFT browsing and internal-ledger trading.'],
            ['key' => 'p2p_merchant_onboarding', 'name' => 'P2P Merchant Onboarding', 'description' => 'Allow new users to apply for P2P merchant status.'],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::updateOrCreate(['key' => $flag['key']], $flag + ['is_enabled' => true]);
        }
    }
}
