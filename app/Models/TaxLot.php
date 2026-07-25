<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxLot extends Model
{
    protected $table = 'tax_lots';

    protected $fillable = [
        'user_id',
        'asset_id',
        'quantity',
        'cost_basis',
        'acquired_at',
        'remaining_quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
            'cost_basis' => 'decimal:8',
            'acquired_at' => 'datetime',
            'remaining_quantity' => 'decimal:8',
        ];
    }

}
