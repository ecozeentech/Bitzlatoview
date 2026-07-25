<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiBotPerformance extends Model
{
    protected $guarded = [];

    public function bot(): BelongsTo
    {
        return $this->belongsTo(AiBot::class, 'ai_bot_id');
    }
}
