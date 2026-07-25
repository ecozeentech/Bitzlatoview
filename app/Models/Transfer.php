<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    protected $table = 'transfers';

    protected $fillable = [
        'uuid',
        'user_id',
        'from_wallet_account_id',
        'to_wallet_account_id',
        'asset_id',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
        ];
    }

}
