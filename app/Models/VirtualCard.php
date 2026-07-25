<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualCard extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fundingWallet(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'funding_wallet_account_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CardTransaction::class, 'virtual_card_id')->latest('occurred_at');
    }
}
