<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuturesPosition extends Model
{
    protected $table = 'futures_positions';

    protected $fillable = [
        'uuid',
        'user_id',
        'futures_market_id',
        'side',
        'margin_mode',
        'leverage',
        'size',
        'entry_price',
        'mark_price',
        'liquidation_price',
        'unrealized_pnl',
        'margin',
        'status',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'decimal:8',
            'entry_price' => 'decimal:8',
            'mark_price' => 'decimal:8',
            'liquidation_price' => 'decimal:8',
            'unrealized_pnl' => 'decimal:8',
            'margin' => 'decimal:8',
            'is_simulated' => 'boolean',
        ];
    }

}
