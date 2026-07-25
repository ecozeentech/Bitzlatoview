<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraderProfile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }
}
