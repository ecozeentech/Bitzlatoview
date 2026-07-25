<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2PEscrow extends Model
{
    protected $table = 'p2p_escrows';

    protected $fillable = [
        'p2p_order_id',
        'wallet_account_id',
        'asset_id',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
        ];
    }

}
