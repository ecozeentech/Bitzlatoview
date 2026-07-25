<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $table = 'assets';

    protected $fillable = [
        'symbol',
        'name',
        'type',
        'decimals',
        'is_active',
        'icon',
        'mock_price_usd',
        'change_24h',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'mock_price_usd' => 'decimal:8',
            'change_24h' => 'decimal:4',
            'metadata' => 'array',
        ];
    }

}
