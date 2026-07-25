<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mt5Account extends Model
{
    protected $guarded = [];

    protected $hidden = ['encrypted_credentials'];

    protected function casts(): array
    {
        return ['last_sync_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Mt5Position::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(Mt5SyncLog::class)->latest('synced_at');
    }
}
