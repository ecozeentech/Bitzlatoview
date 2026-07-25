<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MT5Account extends Model
{
    protected $table = 'mt5_accounts';

    protected $fillable = [
        'user_id',
        'broker_name',
        'mt5_login',
        'server_name',
        'account_type',
        'leverage',
        'currency',
        'status',
        'encrypted_credentials',
        'last_sync_at',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'last_sync_at' => 'datetime',
            'is_simulated' => 'boolean',
        ];
    }

}
