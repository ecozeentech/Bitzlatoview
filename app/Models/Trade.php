<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    protected $table = 'trades';

    protected $fillable = [
        'uuid',
        'order_id',
        'user_id',
        'market_pair_id',
        'side',
        'price',
        'quantity',
        'fee',
        'fee_asset',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'quantity' => 'decimal:8',
            'fee' => 'decimal:8',
            'is_simulated' => 'boolean',
        ];
    }

}
