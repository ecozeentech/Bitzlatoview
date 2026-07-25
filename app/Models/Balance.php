<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balance extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'available' => 'decimal:18',
            'locked' => 'decimal:18',
        ];
    }

    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function total(): float
    {
        return (float) $this->available + (float) $this->locked;
    }
}
