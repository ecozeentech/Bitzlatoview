<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForexOrder extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pair(): BelongsTo
    {
        return $this->belongsTo(ForexPair::class, 'forex_pair_id');
    }
}
