<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiningPackage extends Model
{
    protected $table = 'mining_packages';

    protected $fillable = [
        'name',
        'asset_id',
        'hashrate',
        'hashrate_unit',
        'term_days',
        'price',
        'price_asset_id',
        'maintenance_fee_daily',
        'estimated_daily_reward',
        'risk_disclosure',
        'is_published',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'hashrate' => 'decimal:4',
            'price' => 'decimal:8',
            'maintenance_fee_daily' => 'decimal:8',
            'estimated_daily_reward' => 'decimal:8',
            'is_published' => 'boolean',
            'is_simulated' => 'boolean',
        ];
    }

}
