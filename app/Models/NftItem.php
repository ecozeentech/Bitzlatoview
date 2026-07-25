<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NftItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rarity' => 'array',
            'is_listed' => 'boolean',
        ];
    }
}
