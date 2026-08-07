<?php

namespace Database\Seeders;

use App\Models\TraderProfile;
use Illuminate\Database\Seeder;

class TraderSeeder extends Seeder
{
    public function run(): void
    {
        $traders = [
            ['display_name' => 'Aurora Quant', 'category' => 'crypto', 'risk_score' => 42, 'return_30d_pct' => 8.4, 'return_90d_pct' => 21.6, 'max_drawdown_pct' => 9.2, 'win_rate_pct' => 63.5, 'followers_count' => 4820, 'is_verified' => true, 'is_featured' => true, 'strategy' => 'Momentum-based BTC/ETH swing trading with strict stop-losses.'],
            ['display_name' => 'Nova FX Desk', 'category' => 'forex', 'risk_score' => 55, 'return_30d_pct' => 5.1, 'return_90d_pct' => 14.3, 'max_drawdown_pct' => 11.8, 'win_rate_pct' => 58.2, 'followers_count' => 2310, 'is_verified' => true, 'strategy' => 'Major pair carry-trade and breakout strategy on 4H timeframes.'],
            ['display_name' => 'Helix Futures', 'category' => 'futures', 'risk_score' => 78, 'return_30d_pct' => 15.2, 'return_90d_pct' => 9.4, 'max_drawdown_pct' => 24.6, 'win_rate_pct' => 51.0, 'followers_count' => 1560, 'is_verified' => true, 'strategy' => 'High-leverage perpetual futures scalping. High risk.'],
            ['display_name' => 'Vertex Equities', 'category' => 'stock', 'risk_score' => 33, 'return_30d_pct' => 3.8, 'return_90d_pct' => 11.2, 'max_drawdown_pct' => 6.5, 'win_rate_pct' => 67.8, 'followers_count' => 3040, 'is_verified' => true, 'strategy' => 'Blue-chip tech swing trades with fundamental screening.'],
            ['display_name' => 'Cobalt Digital Assets', 'category' => 'crypto', 'risk_score' => 61, 'return_30d_pct' => 11.9, 'return_90d_pct' => 26.7, 'max_drawdown_pct' => 15.3, 'win_rate_pct' => 55.4, 'followers_count' => 2870, 'is_verified' => false, 'strategy' => 'Altcoin rotation strategy targeting mid-cap breakouts.'],
        ];

        foreach ($traders as $trader) {
            TraderProfile::updateOrCreate(
                ['display_name' => $trader['display_name']],
                $trader + ['bio' => 'Performance figures are disclosed estimates pending independent verification of a real trading track record. Past performance does not guarantee future results.', 'status' => 'active']
            );
        }
    }
}
