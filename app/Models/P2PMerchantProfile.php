<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2PMerchantProfile extends Model
{
    protected $table = 'p2p_merchant_profiles';

    protected $fillable = [
        'user_id',
        'is_verified',
        'completed_trades',
        'completion_rate',
        'positive_feedback_rate',
        'avg_release_minutes',
        'is_online',
        'terms',
        'auto_reply',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'completion_rate' => 'decimal:2',
            'positive_feedback_rate' => 'decimal:2',
            'is_online' => 'boolean',
        ];
    }

}
