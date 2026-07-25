<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2PFeedback extends Model
{
    protected $table = 'p2p_feedback';

    protected $fillable = [
        'p2p_order_id',
        'from_user_id',
        'to_user_id',
        'is_positive',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'is_positive' => 'boolean',
        ];
    }

}
