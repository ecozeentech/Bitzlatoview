<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2POrder extends Model
{
    protected $table = 'p2p_orders';

    protected $fillable = [
        'uuid',
        'ad_id',
        'buyer_id',
        'seller_id',
        'asset_id',
        'fiat_currency',
        'crypto_amount',
        'fiat_amount',
        'price',
        'payment_method',
        'status',
        'payment_deadline',
        'paid_at',
        'released_at',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'crypto_amount' => 'decimal:8',
            'fiat_amount' => 'decimal:2',
            'price' => 'decimal:8',
            'payment_deadline' => 'datetime',
            'paid_at' => 'datetime',
            'released_at' => 'datetime',
            'is_simulated' => 'boolean',
        ];
    }

}
