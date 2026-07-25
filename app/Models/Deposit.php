<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    protected $table = 'deposits';

    protected $fillable = [
        'uuid',
        'user_id',
        'wallet_account_id',
        'asset_id',
        'network_id',
        'amount',
        'status',
        'address',
        'tx_hash',
        'confirmations',
        'is_simulated',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'is_simulated' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
