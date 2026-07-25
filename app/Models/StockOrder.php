<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOrder extends Model
{
    protected $table = 'stock_orders';

    protected $fillable = [
        'uuid',
        'user_id',
        'stock_instrument_id',
        'side',
        'type',
        'quantity',
        'price',
        'status',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:6',
            'price' => 'decimal:4',
            'is_simulated' => 'boolean',
        ];
    }

}
