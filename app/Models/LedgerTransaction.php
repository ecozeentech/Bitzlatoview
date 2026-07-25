<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerTransaction extends Model
{
    protected $table = 'ledger_transactions';

    protected $fillable = [
        'uuid',
        'idempotency_key',
        'type',
        'status',
        'user_id',
        'reference_type',
        'reference_id',
        'description',
        'created_by',
        'approved_by',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function entries()
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
