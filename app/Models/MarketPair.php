<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarketPair extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function baseAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'base_asset_id');
    }

    public function quoteAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'quote_asset_id');
    }

    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function candles(): HasMany
    {
        return $this->hasMany(Candle::class);
    }
}
