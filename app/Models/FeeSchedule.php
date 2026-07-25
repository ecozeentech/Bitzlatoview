<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeSchedule extends Model
{
    protected $table = 'fee_schedules';

    protected $fillable = [
        'name',
        'module',
        'maker_fee_bps',
        'taker_fee_bps',
        'flat_fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'maker_fee_bps' => 'decimal:4',
            'taker_fee_bps' => 'decimal:4',
            'flat_fee' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

}
