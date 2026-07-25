<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuturesMarket extends Model
{
    protected $table = 'futures_markets';

    protected $fillable = [
        'symbol',
        'base_asset_id',
        'contract_type',
        'mark_price',
        'index_price',
        'funding_rate',
        'max_leverage',
        'is_active',
        'paper_only',
    ];

    protected function casts(): array
    {
        return [
            'mark_price' => 'decimal:8',
            'index_price' => 'decimal:8',
            'funding_rate' => 'decimal:8',
            'is_active' => 'boolean',
            'paper_only' => 'boolean',
        ];
    }

}
