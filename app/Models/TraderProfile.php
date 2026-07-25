<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TraderProfile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean', 'is_featured' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(CopyAllocation::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(TraderPerformanceSnapshot::class);
    }
}
