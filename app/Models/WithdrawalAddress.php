<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WithdrawalAddress extends Model
{
    protected $table = 'withdrawal_addresses';

    protected $fillable = [
        'user_id',
        'asset_id',
        'network_id',
        'label',
        'address',
        'is_whitelisted',
        'cooldown_until',
    ];

    protected function casts(): array
    {
        return [
            'is_whitelisted' => 'boolean',
            'cooldown_until' => 'datetime',
        ];
    }

}
