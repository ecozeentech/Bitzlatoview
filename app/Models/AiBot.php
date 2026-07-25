<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiBot extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'supported_assets' => 'array',
        ];
    }
}
