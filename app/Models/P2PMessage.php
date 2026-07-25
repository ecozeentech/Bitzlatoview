<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2PMessage extends Model
{
    protected $table = 'p2p_messages';

    protected $fillable = [
        'p2p_order_id',
        'user_id',
        'body',
        'attachment_path',
    ];

}
