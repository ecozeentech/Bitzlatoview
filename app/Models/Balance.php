<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Balance extends Model
{
    protected $table = 'balances';

    protected $fillable = [
        'wallet_account_id',
        'asset_id',
        'available',
        'locked',
    ];

    protected function casts(): array
    {
        return [
            'available' => 'decimal:8',
            'locked' => 'decimal:8',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function walletAccount()
    {
        return $this->belongsTo(WalletAccount::class);
    }

    public function total(): string
    {
        return bcadd((string) $this->available, (string) $this->locked, 8);
    }
}
