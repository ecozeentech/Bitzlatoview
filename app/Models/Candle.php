<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candle extends Model
{
    protected $table = 'candles';

    protected $fillable = [
        'market_pair_id',
        'interval',
        'open_time',
        'open',
        'high',
        'low',
        'close',
        'volume',
    ];

    protected function casts(): array
    {
        return [
            'open_time' => 'datetime',
            'open' => 'decimal:8',
            'high' => 'decimal:8',
            'low' => 'decimal:8',
            'close' => 'decimal:8',
            'volume' => 'decimal:8',
        ];
    }

}
