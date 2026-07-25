<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class P2POrder extends Model
{
    protected $table = 'p2p_orders';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'released_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(P2PAd::class, 'p2p_ad_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(P2PMessage::class, 'p2p_order_id')->orderBy('created_at');
    }

    public function appeal(): HasOne
    {
        return $this->hasOne(P2PAppeal::class, 'p2p_order_id')->latestOfMany();
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(P2PFeedback::class, 'p2p_order_id');
    }
}
