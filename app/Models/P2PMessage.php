<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2PMessage extends Model
{
    protected $table = 'p2p_messages';

    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(P2POrder::class, 'p2p_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
