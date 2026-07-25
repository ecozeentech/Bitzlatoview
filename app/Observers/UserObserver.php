<?php

namespace App\Observers;

use App\Models\User;
use App\Models\WalletAccount;
use Illuminate\Support\Str;

class UserObserver
{
    public function created(User $user): void
    {
        foreach (WalletAccount::TYPES as $type) {
            WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $type]);
        }

        if (! $user->referral_code) {
            $user->forceFill(['referral_code' => Str::upper(Str::random(8))])->save();
        }
    }
}
