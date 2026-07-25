<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminNote extends Model
{
    protected $table = 'admin_notes';

    protected $fillable = [
        'user_id',
        'admin_id',
        'note',
        'visibility',
    ];

}
