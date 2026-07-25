<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockPosition extends Model
{
    protected $table = 'stock_positions';

    protected $fillable = [
        'user_id',
        'stock_instrument_id',
        'quantity',
        'avg_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:6',
            'avg_cost' => 'decimal:4',
        ];
    }


    public function stockInstrument()
    {
        return $this->belongsTo(StockInstrument::class);
    }
}
