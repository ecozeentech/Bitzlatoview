<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerEntry extends Model
{
    protected $table = 'ledger_entries';

    protected $fillable = [
        'ledger_transaction_id',
        'wallet_account_id',
        'asset_id',
        'entry_type',
        'balance_bucket',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'balance_after' => 'decimal:8',
            'metadata' => 'array',
        ];
    }

}
