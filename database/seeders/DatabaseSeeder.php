<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AssetSeeder::class,
            NetworkSeeder::class,
            MarketSeeder::class,
            UserSeeder::class,
            P2PSeeder::class,
            TraderSeeder::class,
            AiBotSeeder::class,
            MiningSeeder::class,
            InvestmentSeeder::class,
            StockForexFuturesSeeder::class,
            NftSeeder::class,
            BlogNewsSeeder::class,
            EmailTemplateSeeder::class,
            BillingSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
