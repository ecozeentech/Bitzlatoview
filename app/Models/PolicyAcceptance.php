<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolicyAcceptance extends Model
{
    protected $table = 'policy_acceptances';

    protected $fillable = [
        'user_id',
        'policy_type',
        'version',
        'ip_address',
    ];

}
