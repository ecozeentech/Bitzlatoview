<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualAdjustment extends Model
{
    protected $table = 'manual_adjustments';

    protected $fillable = [
        'user_id',
        'wallet_account_id',
        'asset_id',
        'amount',
        'direction',
        'status',
        'reason',
        'evidence_url',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'approved_at' => 'datetime',
        ];
    }

}
