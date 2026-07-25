<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MiningReward extends Model
{
    protected $guarded = [];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(MiningContract::class, 'mining_contract_id');
    }
}
