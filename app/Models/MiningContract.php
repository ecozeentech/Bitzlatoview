<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiningContract extends Model
{
    protected $table = 'mining_contracts';

    protected $fillable = [
        'uuid',
        'user_id',
        'mining_package_id',
        'wallet_account_id',
        'hashrate',
        'purchase_amount',
        'status',
        'starts_at',
        'ends_at',
        'reward_wallet',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'hashrate' => 'decimal:4',
            'purchase_amount' => 'decimal:8',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_simulated' => 'boolean',
        ];
    }


    public function miningPackage()
    {
        return $this->belongsTo(MiningPackage::class);
    }
}
