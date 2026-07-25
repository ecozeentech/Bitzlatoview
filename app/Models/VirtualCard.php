<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualCard extends Model
{
    protected $table = 'virtual_cards';

    protected $fillable = [
        'uuid',
        'user_id',
        'cardholder_id',
        'nickname',
        'last_four',
        'brand',
        'currency',
        'spending_limit',
        'spent_amount',
        'status',
        'masked_pan',
        'provider_ref',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'spending_limit' => 'decimal:2',
            'spent_amount' => 'decimal:2',
            'is_simulated' => 'boolean',
        ];
    }

}
