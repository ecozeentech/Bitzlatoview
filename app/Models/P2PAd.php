<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2PAd extends Model
{
    protected $table = 'p2p_ads';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['payment_method_ids' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(P2POrder::class, 'p2p_ad_id');
    }
}
