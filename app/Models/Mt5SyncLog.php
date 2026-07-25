<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mt5SyncLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['synced_at' => 'datetime'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Mt5Account::class, 'mt5_account_id');
    }
}
