<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CopyTraderProfile extends Model
{
    protected $table = 'copy_trader_profiles';

    protected $fillable = [
        'user_id',
        'display_name',
        'category',
        'bio',
        'strategy',
        'is_verified',
        'is_featured',
        'status',
        'risk_level',
        'return_30d',
        'return_90d',
        'max_drawdown',
        'win_rate',
        'followers',
        'avatar',
        'assets_traded',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'return_30d' => 'decimal:4',
            'return_90d' => 'decimal:4',
            'max_drawdown' => 'decimal:4',
            'win_rate' => 'decimal:2',
            'assets_traded' => 'array',
        ];
    }

}
