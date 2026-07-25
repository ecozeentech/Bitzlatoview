<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskScore extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'factors' => 'array',
        ];
    }
}
