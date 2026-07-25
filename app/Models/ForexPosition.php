<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForexPosition extends Model
{
    protected $table = 'forex_positions';

    protected $fillable = [
        'user_id',
        'forex_pair_id',
        'side',
        'lots',
        'entry_price',
        'unrealized_pnl',
        'status',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'lots' => 'decimal:4',
            'entry_price' => 'decimal:6',
            'unrealized_pnl' => 'decimal:6',
            'is_simulated' => 'boolean',
        ];
    }


    public function forexPair()
    {
        return $this->belongsTo(ForexPair::class);
    }
}
