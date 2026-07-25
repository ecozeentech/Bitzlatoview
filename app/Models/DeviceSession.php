<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceSession extends Model
{
    protected $table = 'device_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'device_name',
        'ip_address',
        'user_agent',
        'is_current',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

}
