<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\House;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        House::user();

        User::updateOrCreate(
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

        User::updateOrCreate(
            ['email' => 'testuser@bitzlatoview.com'],
            [
                'name' => 'QA Test Account',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'kyc_status' => 'not_started',
                'country' => 'United Kingdom',
                'city' => 'London',
                'phone' => '+44 7000 000000',
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'risk_disclosure_accepted_at' => now(),
            ]
        );

        // Intentionally no seeded wallet balances here. Every account, including internal
        // QA accounts, must fund its wallet through the real deposit flow (Admin > Payment
        // Settings + user Deposit page) or an audited admin balance adjustment — never a
        // seeder — so there is no code path that fabricates a real-looking balance.
    }
}
