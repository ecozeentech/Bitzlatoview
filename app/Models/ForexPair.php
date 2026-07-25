<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForexPair extends Model
{
    protected $guarded = [];

    public function orders(): HasMany
    {
        return $this->hasMany(ForexOrder::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(ForexPosition::class);
    }
}
