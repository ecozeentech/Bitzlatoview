<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletAccount;

class WalletProvisioningService
{
    public function provision(User $user): void
    {
        foreach (['PRIMARY', 'TRADING', 'INVESTMENT'] as $type) {
            WalletAccount::query()->firstOrCreate(
                ['user_id' => $user->id, 'type' => $type],
                ['status' => 'active']
            );
        }
    }
}
