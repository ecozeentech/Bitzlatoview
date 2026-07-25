<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForexOrder extends Model
{
    protected $table = 'forex_orders';

    protected $fillable = [
        'uuid',
        'user_id',
        'forex_pair_id',
        'side',
        'lots',
        'price',
        'leverage',
        'status',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'lots' => 'decimal:4',
            'price' => 'decimal:6',
            'is_simulated' => 'boolean',
        ];
    }

}
