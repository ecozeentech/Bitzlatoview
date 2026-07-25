<?php

namespace App\Support;

use App\Models\User;
use App\Models\WalletAccount;

/**
 * The "House" is the platform's own system account. It acts as the ledger counterparty
 * for operations that are not naturally balanced between two real users, e.g.:
 *  - Crediting a user on deposit (house is debited, representing custody received)
 *  - Debiting a user on withdrawal (house is credited back)
 *  - Simulated spot/futures/swap fills against internal liquidity (paper trading mode)
 *  - Fee collection, mining/bot/investment reward payouts
 *
 * This keeps every ledger transaction double-entry-balanced without needing a full
 * chart-of-accounts implementation for the MVP.
 */
class House
{
    public const SYSTEM_EMAIL = 'house@bitzlatoview.internal';

    protected static ?User $cached = null;

    public static function user(): User
    {
        if (self::$cached) {
            return self::$cached;
        }

        return self::$cached = User::firstOrCreate(
            ['email' => self::SYSTEM_EMAIL],
            [
                'name' => 'Bitzlatoview System Account',
                'password' => bcrypt(bin2hex(random_bytes(16))),
                'role' => 'system',
                'status' => 'active',
                'kyc_status' => 'approved',
                'email_verified_at' => now(),
            ]
        );
    }

    public static function wallet(string $type): WalletAccount
    {
        return WalletAccount::firstOrCreate([
            'user_id' => self::user()->id,
            'type' => $type,
        ]);
    }
}
