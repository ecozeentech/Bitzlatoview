<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailSuppression extends Model
{
    protected $table = 'email_suppressions';

    protected $fillable = [
        'email',
        'reason',
    ];

}
