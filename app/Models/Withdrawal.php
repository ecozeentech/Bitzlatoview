<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Withdrawal extends Model
{
    protected $table = 'withdrawals';

    protected $fillable = [
        'uuid',
        'user_id',
        'wallet_account_id',
        'asset_id',
        'network_id',
        'amount',
        'fee',
        'destination_address',
        'status',
        'is_simulated',
        'approved_by',
        'approved_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'fee' => 'decimal:8',
            'is_simulated' => 'boolean',
            'approved_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walletAccount()
    {
        return $this->belongsTo(WalletAccount::class);
    }
}
