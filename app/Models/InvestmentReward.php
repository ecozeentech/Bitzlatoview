<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentReward extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['credited_at' => 'datetime'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(InvestmentSubscription::class, 'investment_subscription_id');
    }
}
