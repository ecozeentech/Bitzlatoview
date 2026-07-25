<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NftListing extends Model
{
    protected $table = 'nft_listings';

    protected $fillable = [
        'nft_item_id',
        'seller_id',
        'price',
        'currency',
        'status',
        'is_simulated',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'is_simulated' => 'boolean',
        ];
    }

}
