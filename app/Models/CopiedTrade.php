<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CopiedTrade extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['opened_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(CopyAllocation::class, 'copy_allocation_id');
    }
}
