<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuturesFundingPayment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(FuturesPosition::class, 'futures_position_id');
    }
}
