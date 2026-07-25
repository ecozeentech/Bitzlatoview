<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MT5Position extends Model
{
    protected $table = 'mt5_positions';

    protected $fillable = [
        'mt5_account_id',
        'symbol',
        'side',
        'volume',
        'open_price',
        'current_price',
        'profit',
        'status',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'volume' => 'decimal:4',
            'open_price' => 'decimal:6',
            'current_price' => 'decimal:6',
            'profit' => 'decimal:6',
            'is_simulated' => 'boolean',
        ];
    }


    public function mt5Account()
    {
        return $this->belongsTo(MT5Account::class);
    }
}
