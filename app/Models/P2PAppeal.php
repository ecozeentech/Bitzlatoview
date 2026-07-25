<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2PAppeal extends Model
{
    protected $table = 'p2p_appeals';

    protected $fillable = [
        'p2p_order_id',
        'opened_by',
        'reason',
        'evidence_url',
        'status',
        'admin_resolution',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

}
