<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiBotAllocation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'stopped_at' => 'datetime', 'unlocks_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bot(): BelongsTo
    {
        return $this->belongsTo(AiBot::class, 'ai_bot_id');
    }

    public function trades(): HasMany
    {
        return $this->hasMany(AiBotTrade::class, 'ai_bot_allocation_id');
    }
}
