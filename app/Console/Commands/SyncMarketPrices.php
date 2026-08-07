<?php

namespace App\Console\Commands;

use App\Services\MarketDataService;
use Illuminate\Console\Command;

class SyncMarketPrices extends Command
{
    protected $signature = 'market:sync-prices';

    protected $description = 'Fetch live crypto prices from CoinGecko and update the quotes table';

    public function handle(MarketDataService $service): int
    {
        $updated = $service->syncCryptoQuotes();

        $this->info("Synced {$updated} market quote(s) from CoinGecko.");

        return self::SUCCESS;
    }
}
