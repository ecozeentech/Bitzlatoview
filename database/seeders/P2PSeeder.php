<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\P2PAd;
use App\Models\P2PMerchantProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class P2PSeeder extends Seeder
{
    public function run(): void
    {
        $usdt = Asset::where('symbol', 'USDT')->first();
        $btc = Asset::where('symbol', 'BTC')->first();

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

            P2PAd::updateOrCreate(
                ['user_id' => $user->id, 'asset_id' => $usdt->id, 'side' => 'sell'],
                [
                    'fiat_currency' => ['USD', 'NGN', 'EUR'][$i],
                    'price_type' => 'fixed',
                    'price' => [1.001, 1550.00, 0.925][$i],
                    'min_limit' => 20,
                    'max_limit' => 5000,
                    'available_amount' => 15000,
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
                    'max_limit' => 8000,
                    'available_amount' => 0.8,
                    'payment_method_ids' => [],
                    'terms' => 'Fast release, verified merchant.',
                    'region' => 'Global',
                    'status' => 'active',
                ]
            );
        }
    }
}
