<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiBot extends Model
{
    protected $table = 'ai_bots';

    protected $fillable = [
        'name',
        'slug',
        'strategy_type',
        'description',
        'risk_level',
        'risk_score',
        'max_drawdown',
        'simulated_return_30d',
        'min_allocation',
        'supported_assets',
        'is_active',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'risk_score' => 'decimal:2',
            'max_drawdown' => 'decimal:4',
            'simulated_return_30d' => 'decimal:4',
            'min_allocation' => 'decimal:8',
            'supported_assets' => 'array',
            'is_active' => 'boolean',
            'is_simulated' => 'boolean',
        ];
    }

}
