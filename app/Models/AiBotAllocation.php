<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiBotAllocation extends Model
{
    protected $table = 'ai_bot_allocations';

    protected $fillable = [
        'user_id',
        'ai_bot_id',
        'wallet_account_id',
        'asset_id',
        'amount',
        'status',
        'pnl',
        'lock_until',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'pnl' => 'decimal:8',
            'lock_until' => 'datetime',
            'is_simulated' => 'boolean',
        ];
    }


    public function aiBot()
    {
        return $this->belongsTo(AiBot::class);
    }
}
