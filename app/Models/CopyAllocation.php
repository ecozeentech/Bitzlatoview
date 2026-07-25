<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CopyAllocation extends Model
{
    protected $table = 'copy_allocations';

    protected $fillable = [
        'user_id',
        'copy_trader_profile_id',
        'wallet_account_id',
        'asset_id',
        'allocation_amount',
        'copy_ratio',
        'stop_loss',
        'take_profit',
        'max_position_size',
        'status',
        'pnl',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'allocation_amount' => 'decimal:8',
            'copy_ratio' => 'decimal:4',
            'stop_loss' => 'decimal:4',
            'take_profit' => 'decimal:4',
            'max_position_size' => 'decimal:8',
            'pnl' => 'decimal:8',
            'is_simulated' => 'boolean',
        ];
    }


    public function copyTraderProfile()
    {
        return $this->belongsTo(CopyTraderProfile::class);
    }
}
