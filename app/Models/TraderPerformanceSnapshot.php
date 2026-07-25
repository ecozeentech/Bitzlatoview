<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraderPerformanceSnapshot extends Model
{
    protected $guarded = [];

    public function trader(): BelongsTo
    {
        return $this->belongsTo(TraderProfile::class, 'trader_profile_id');
    }
}
