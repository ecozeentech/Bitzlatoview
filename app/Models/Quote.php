<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $table = 'quotes';

    protected $fillable = [
        'market_pair_id',
        'bid',
        'ask',
        'last',
        'quoted_at',
    ];

    protected function casts(): array
    {
        return [
            'bid' => 'decimal:8',
            'ask' => 'decimal:8',
            'last' => 'decimal:8',
            'quoted_at' => 'datetime',
        ];
    }

}
