<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForexPair extends Model
{
    protected $table = 'forex_pairs';

    protected $fillable = [
        'symbol',
        'base_currency',
        'quote_currency',
        'bid',
        'ask',
        'spread',
        'is_active',
        'paper_only',
    ];

    protected function casts(): array
    {
        return [
            'bid' => 'decimal:6',
            'ask' => 'decimal:6',
            'spread' => 'decimal:6',
            'is_active' => 'boolean',
            'paper_only' => 'boolean',
        ];
    }

}
