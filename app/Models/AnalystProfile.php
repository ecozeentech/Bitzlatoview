<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalystProfile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'credential_verified' => 'boolean',
        ];
    }
}
