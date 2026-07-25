<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NftCollection extends Model
{
    protected $table = 'nft_collections';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_image',
        'floor_price',
        'volume_24h',
        'owners',
        'items_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'floor_price' => 'decimal:8',
            'volume_24h' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

}
