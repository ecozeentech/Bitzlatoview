<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentSubscription extends Model
{
    protected $table = 'investment_subscriptions';

    protected $fillable = [
        'user_id',
        'investment_product_id',
        'wallet_account_id',
        'amount',
        'status',
        'lock_until',
        'accrued_reward',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'lock_until' => 'datetime',
            'accrued_reward' => 'decimal:8',
            'is_simulated' => 'boolean',
        ];
    }

}
