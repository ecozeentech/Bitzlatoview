<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Network;
use Illuminate\Database\Seeder;

class NetworkSeeder extends Seeder
{
    public function run(): void
    {
        $networks = [
            ['name' => 'Bitcoin', 'code' => 'BTC'],
            ['name' => 'Ethereum (ERC-20)', 'code' => 'ETH'],
            ['name' => 'BNB Smart Chain', 'code' => 'BSC'],
            ['name' => 'Polygon', 'code' => 'MATIC'],
            ['name' => 'Solana', 'code' => 'SOL'],
            ['name' => 'Tron (TRC-20)', 'code' => 'TRX'],
        ];

        foreach ($networks as $network) {
            Network::updateOrCreate(['code' => $network['code']], $network + ['is_active' => true]);
        }

        $map = [
            'BTC' => ['BTC'],
            'ETH' => ['ETH'],
            'USDT' => ['ETH', 'BSC', 'TRX', 'MATIC'],
            'USDC' => ['ETH', 'BSC', 'MATIC', 'SOL'],
            'SOL' => ['SOL'],
            'XRP' => ['BTC'],
            'DOGE' => ['BTC'],
            'LTC' => ['BTC'],
        ];

        foreach ($map as $assetSymbol => $networkCodes) {
            $asset = Asset::where('symbol', $assetSymbol)->first();
            if (! $asset) {
                continue;
            }

            foreach ($networkCodes as $code) {
                $network = Network::where('code', $code)->first();
                if (! $network) {
                    continue;
                }

                $asset->networks()->syncWithoutDetaching([
                    $network->id => [
                        'deposit_min' => 0.0001,
                        'withdrawal_fee' => 0.0005,
                        'confirmations_required' => 12,
                    ],
                ]);
            }
        }
    }
}
