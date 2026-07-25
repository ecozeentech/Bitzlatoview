<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cardholder extends Model
{
    protected $table = 'cardholders';

    protected $fillable = [
        'user_id',
        'legal_name',
        'status',
        'provider_ref',
    ];

}
