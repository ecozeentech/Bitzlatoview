<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailLog extends Model
{
    protected $table = 'email_logs';

    protected $fillable = [
        'user_id',
        'recipient',
        'subject',
        'template',
        'provider',
        'provider_message_id',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
        ];
    }

}
