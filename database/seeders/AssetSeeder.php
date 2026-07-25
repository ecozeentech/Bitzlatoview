<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['symbol' => 'BTC', 'name' => 'Bitcoin', 'type' => 'crypto', 'decimals' => 8],
            ['symbol' => 'ETH', 'name' => 'Ethereum', 'type' => 'crypto', 'decimals' => 18],
            ['symbol' => 'USDT', 'name' => 'Tether USD', 'type' => 'crypto', 'decimals' => 6],
            ['symbol' => 'USDC', 'name' => 'USD Coin', 'type' => 'crypto', 'decimals' => 6],
            ['symbol' => 'SOL', 'name' => 'Solana', 'type' => 'crypto', 'decimals' => 9],
            ['symbol' => 'XRP', 'name' => 'Ripple', 'type' => 'crypto', 'decimals' => 6],
            ['symbol' => 'DOGE', 'name' => 'Dogecoin', 'type' => 'crypto', 'decimals' => 8],
            ['symbol' => 'LTC', 'name' => 'Litecoin', 'type' => 'crypto', 'decimals' => 8],
            ['symbol' => 'USD', 'name' => 'US Dollar', 'type' => 'fiat', 'decimals' => 2],
        ];

        foreach ($assets as $asset) {
            Asset::updateOrCreate(['symbol' => $asset['symbol']], $asset + ['is_active' => true]);
        }
    }
}
