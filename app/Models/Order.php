<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'uuid',
        'user_id',
        'market_pair_id',
        'side',
        'type',
        'status',
        'price',
        'stop_price',
        'quantity',
        'filled_quantity',
        'avg_fill_price',
        'fee',
        'is_simulated',
        'filled_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'stop_price' => 'decimal:8',
            'quantity' => 'decimal:8',
            'filled_quantity' => 'decimal:8',
            'avg_fill_price' => 'decimal:8',
            'fee' => 'decimal:8',
            'is_simulated' => 'boolean',
            'filled_at' => 'datetime',
        ];
    }

}
