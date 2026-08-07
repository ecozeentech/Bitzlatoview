<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\P2PAd;
use App\Models\P2PMerchantProfile;
use App\Models\User;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class P2PSeeder extends Seeder
{
    public function run(): void
    {
        $usdt = Asset::where('symbol', 'USDT')->first();
        $btc = Asset::where('symbol', 'BTC')->first();
        $ledger = app(LedgerService::class);
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);

        $merchants = [
            ['name' => 'GoldStar Trading', 'email' => 'merchant1@bitzlatoview.com'],
            ['name' => 'NairaFast Exchange', 'email' => 'merchant2@bitzlatoview.com'],
            ['name' => 'EuroSwift OTC', 'email' => 'merchant3@bitzlatoview.com'],
        ];

        foreach ($merchants as $i => $m) {
            $user = User::updateOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['name'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'kyc_status' => 'approved',
                    'country' => 'Global',
                    'email_verified_at' => now(),
                ]
            );

            $profile = P2PMerchantProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'display_name' => $m['name'],
                    'is_verified' => true,
                    'completed_orders' => 500 + $i * 320,
                    'completion_rate' => 98.5 - $i,
                    'positive_feedback_rate' => 99.1 - $i,
                    'avg_release_minutes' => 8 + $i * 3,
                    'status' => 'active',
                ]
            );

            $primary = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);

            // These are the company's own vetted market-making/merchant accounts, funded from
            // treasury before any real user trades against them — not a free balance handed to
            // an end user. Without this, seeded ads advertise crypto the account doesn't
            // actually hold, and every real order against them fails with "insufficient
            // balance." Keep amounts aligned with each ad's max_limit below.
            $usdtLiquidity = 2000;
            $btcLiquidity = 0.05;

            foreach ([[$usdt, $usdtLiquidity], [$btc, $btcLiquidity]] as [$asset, $amount]) {
                $ledger->post(
                    entries: [
                        ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                        ['wallet_account_id' => $primary->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
                    ],
                    referenceType: 'merchant_treasury_funding',
                    description: "Treasury-funded P2P merchant liquidity for {$m['name']}",
                    idempotencyKey: 'p2p-merchant-liquidity-'.$user->id.'-'.$asset->symbol,
                );
            }

            P2PAd::updateOrCreate(
                ['user_id' => $user->id, 'asset_id' => $usdt->id, 'side' => 'sell'],
                [
                    'fiat_currency' => ['USD', 'NGN', 'EUR'][$i],
                    'price_type' => 'fixed',
                    'price' => [1.001, 1550.00, 0.925][$i],
                    'min_limit' => 20,
                    'max_limit' => 500,
                    'available_amount' => $usdtLiquidity,
                    'payment_method_ids' => [],
                    'terms' => 'Please make payment within the countdown timer. Do not include crypto-related notes in your bank transfer.',
                    'auto_reply' => 'Thanks for the order — releasing as soon as payment is confirmed.',
                    'region' => 'Global',
                    'status' => 'active',
                ]
            );

            P2PAd::updateOrCreate(
                ['user_id' => $user->id, 'asset_id' => $btc->id, 'side' => 'buy'],
                [
                    'fiat_currency' => ['USD', 'NGN', 'EUR'][$i],
                    'price_type' => 'floating',
                    'price' => [64100, 99350000, 59250][$i],
                    'min_limit' => 50,
                    'max_limit' => 500,
                    'available_amount' => $btcLiquidity,
                    'payment_method_ids' => [],
                    'terms' => 'Fast release, verified merchant.',
                    'region' => 'Global',
                    'status' => 'active',
                ]
            );
        }
    }
}
