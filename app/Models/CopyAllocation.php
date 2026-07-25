<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CopyAllocation extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trader(): BelongsTo
    {
        return $this->belongsTo(TraderProfile::class, 'trader_profile_id');
    }

    public function trades(): HasMany
    {
        return $this->hasMany(CopiedTrade::class);
    }
}
