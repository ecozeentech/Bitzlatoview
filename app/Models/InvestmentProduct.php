<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentProduct extends Model
{
    protected $table = 'investment_products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'asset_id',
        'apy_estimate',
        'lock_days',
        'min_amount',
        'is_active',
        'is_simulated',
        'risk_disclosure',
    ];

    protected function casts(): array
    {
        return [
            'apy_estimate' => 'decimal:4',
            'min_amount' => 'decimal:8',
            'is_active' => 'boolean',
            'is_simulated' => 'boolean',
        ];
    }

}
