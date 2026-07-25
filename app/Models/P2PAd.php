<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class P2PAd extends Model
{
    protected $table = 'p2p_ads';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payment_method_ids' => 'array',
        ];
    }
}
