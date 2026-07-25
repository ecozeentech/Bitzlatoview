<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletAccount extends Model
{
    protected $guarded = [];

    public const TYPE_PRIMARY = 'primary';

    public const TYPE_TRADING = 'trading';

    public const TYPE_INVESTMENT = 'investment';

    public const TYPES = [self::TYPE_PRIMARY, self::TYPE_TRADING, self::TYPE_INVESTMENT];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(Balance::class);
    }

    public function balanceFor(Asset $asset): Balance
    {
        return $this->balances()->firstOrCreate(
            ['asset_id' => $asset->id],
            ['available' => 0, 'locked' => 0]
        );
    }

    public function label(): string
    {
        return match ($this->type) {
            self::TYPE_PRIMARY => 'Primary Wallet',
            self::TYPE_TRADING => 'Trading Wallet',
            self::TYPE_INVESTMENT => 'Investment Wallet',
            default => ucfirst($this->type),
        };
    }
}
