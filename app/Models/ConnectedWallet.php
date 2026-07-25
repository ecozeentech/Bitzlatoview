<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConnectedWallet extends Model
{
    protected $table = 'connected_wallets';

    protected $fillable = [
        'user_id',
        'address',
        'chain',
        'wallet_type',
        'label',
        'is_primary',
        'last_connected_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'last_connected_at' => 'datetime',
        ];
    }

}
