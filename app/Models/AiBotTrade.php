<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiBotTrade extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['executed_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(AiBotAllocation::class, 'ai_bot_allocation_id');
    }
}
