<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockInstrument extends Model
{
    protected $guarded = [];

    public function orders(): HasMany
    {
        return $this->hasMany(StockOrder::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(StockPosition::class);
    }
}
