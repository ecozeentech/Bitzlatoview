<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Network extends Model
{
    protected $table = 'networks';

    protected $fillable = [
        'name',
        'code',
        'asset_id',
        'confirmations',
        'min_deposit',
        'withdrawal_fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'min_deposit' => 'decimal:8',
            'withdrawal_fee' => 'decimal:8',
        ];
    }

}
