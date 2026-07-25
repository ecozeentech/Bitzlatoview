<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockInstrument extends Model
{
    protected $table = 'stock_instruments';

    protected $fillable = [
        'symbol',
        'name',
        'exchange',
        'last_price',
        'change_24h',
        'is_active',
        'paper_only',
    ];

    protected function casts(): array
    {
        return [
            'last_price' => 'decimal:4',
            'change_24h' => 'decimal:4',
            'is_active' => 'boolean',
            'paper_only' => 'boolean',
        ];
    }

}
