<?php

namespace Database\Seeders;

use App\Models\AiBot;
use Illuminate\Database\Seeder;

class AiBotSeeder extends Seeder
{
    public function run(): void
    {
        $bots = [
            ['name' => 'Steady Grid BTC', 'strategy_type' => 'grid', 'risk_score' => 28, 'min_allocation' => 50, 'historical_return_pct' => 6.2, 'max_drawdown_pct' => 4.1, 'lock_days' => 0, 'supported_assets' => ['BTC', 'USDT']],
            ['name' => 'Conservative DCA', 'strategy_type' => 'dca', 'risk_score' => 20, 'min_allocation' => 25, 'historical_return_pct' => 4.8, 'max_drawdown_pct' => 3.0, 'lock_days' => 0, 'supported_assets' => ['BTC', 'ETH']],
            ['name' => 'Balanced Trend Follower', 'strategy_type' => 'trend', 'risk_score' => 48, 'min_allocation' => 100, 'historical_return_pct' => 12.5, 'max_drawdown_pct' => 9.7, 'lock_days' => 7, 'supported_assets' => ['BTC', 'ETH', 'SOL']],
            ['name' => 'Aggressive Alt Momentum', 'strategy_type' => 'aggressive', 'risk_score' => 74, 'min_allocation' => 200, 'historical_return_pct' => 22.3, 'max_drawdown_pct' => 19.8, 'lock_days' => 14, 'supported_assets' => ['SOL', 'XRP', 'DOGE']],
            ['name' => 'Arbitrage Scanner (Beta)', 'strategy_type' => 'arbitrage', 'risk_score' => 35, 'min_allocation' => 150, 'historical_return_pct' => 8.9, 'max_drawdown_pct' => 5.5, 'lock_days' => 3, 'supported_assets' => ['USDT', 'USDC']],
        ];

        foreach ($bots as $bot) {
            AiBot::updateOrCreate(
                ['name' => $bot['name']],
                $bot + [
                    'description' => 'AI trading bot is experimental and may lose money. It runs on Bitzlatoview\'s internal strategy engine, not a live connection to an external exchange. No guaranteed returns.',
                    'status' => 'active',
                ]
            );
        }
    }
}
