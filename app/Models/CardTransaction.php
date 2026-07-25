<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardTransaction extends Model
{
    protected $table = 'card_transactions';

    protected $fillable = [
        'virtual_card_id',
        'amount',
        'currency',
        'merchant_name',
        'status',
        'type',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_simulated' => 'boolean',
        ];
    }

}
