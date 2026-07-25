<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiningReward extends Model
{
    protected $table = 'mining_rewards';

    protected $fillable = [
        'mining_contract_id',
        'asset_id',
        'amount',
        'reward_date',
        'status',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'reward_date' => 'date',
            'is_simulated' => 'boolean',
        ];
    }

    public function miningContract()
    {
        return $this->belongsTo(MiningContract::class);
    }
}
