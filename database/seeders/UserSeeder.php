<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        House::user();

        $admin = User::updateOrCreate(
            ['email' => 'admin@bitzlatoview.com'],
            [
                'name' => 'Bitzlatoview Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'kyc_status' => 'approved',
                'country' => 'United States',
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'risk_disclosure_accepted_at' => now(),
            ]
        );

        $verified = User::updateOrCreate(
            ['email' => 'demo@bitzlatoview.com'],
            [
                'name' => 'Demo Verified Trader',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'kyc_status' => 'approved',
                'country' => 'United Kingdom',
                'city' => 'London',
                'phone' => '+44 7000 000000',
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'risk_disclosure_accepted_at' => now(),
            ]
        );

        $unverified = User::updateOrCreate(
            ['email' => 'unverified@bitzlatoview.com'],
            [
                'name' => 'Demo Unverified User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'kyc_status' => 'not_started',
                'country' => 'Nigeria',
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'risk_disclosure_accepted_at' => now(),
            ]
        );

        $ledger = app(LedgerService::class);
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);

        $usdt = Asset::where('symbol', 'USDT')->first();
        $btc = Asset::where('symbol', 'BTC')->first();
        $eth = Asset::where('symbol', 'ETH')->first();

        foreach ([$verified, $unverified] as $user) {
            $primary = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);

            foreach ([[$usdt, 25000], [$btc, 0.35], [$eth, 4.2]] as [$asset, $amount]) {
                $ledger->post(
                    entries: [
                        ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                        ['wallet_account_id' => $primary->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
                    ],
                    referenceType: 'seed_deposit',
                    description: "Seed balance for {$user->email}",
                    idempotencyKey: 'seed-'.Str::slug($user->email).'-'.$asset->symbol,
                );
            }
        }
    }
}
