<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SwapTransaction extends Model
{
    protected $table = 'swap_transactions';

    protected $fillable = [
        'uuid',
        'user_id',
        'from_wallet_account_id',
        'to_wallet_account_id',
        'from_asset_id',
        'to_asset_id',
        'from_amount',
        'to_amount',
        'rate',
        'fee',
        'slippage',
        'price_impact',
        'status',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'from_amount' => 'decimal:8',
            'to_amount' => 'decimal:8',
            'rate' => 'decimal:8',
            'fee' => 'decimal:8',
            'slippage' => 'decimal:4',
            'price_impact' => 'decimal:4',
            'is_simulated' => 'boolean',
        ];
    }

}
