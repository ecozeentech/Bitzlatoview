<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function balances(): HasMany
    {
        return $this->hasMany(Balance::class);
    }

    public function networks()
    {
        return $this->belongsToMany(Network::class, 'asset_networks')
            ->withPivot(['deposit_min', 'withdrawal_fee', 'confirmations_required', 'contract_address']);
    }

    public function isCrypto(): bool
    {
        return $this->type === 'crypto';
    }
}
