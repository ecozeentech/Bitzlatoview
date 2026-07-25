<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketPair extends Model
{
    protected $table = 'market_pairs';

    protected $fillable = [
        'symbol',
        'base_asset_id',
        'quote_asset_id',
        'market_type',
        'last_price',
        'change_24h',
        'high_24h',
        'low_24h',
        'volume_24h',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_price' => 'decimal:8',
            'change_24h' => 'decimal:4',
            'high_24h' => 'decimal:8',
            'low_24h' => 'decimal:8',
            'volume_24h' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    public function baseAsset()
    {
        return $this->belongsTo(Asset::class, 'base_asset_id');
    }

    public function quoteAsset()
    {
        return $this->belongsTo(Asset::class, 'quote_asset_id');
    }
}
