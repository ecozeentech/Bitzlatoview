<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CopiedTrade extends Model
{
    protected $table = 'copied_trades';

    protected $fillable = [
        'copy_allocation_id',
        'symbol',
        'side',
        'price',
        'quantity',
        'pnl',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'quantity' => 'decimal:8',
            'pnl' => 'decimal:8',
            'is_simulated' => 'boolean',
        ];
    }

}
