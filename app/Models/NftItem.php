<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NftItem extends Model
{
    protected $table = 'nft_items';

    protected $fillable = [
        'nft_collection_id',
        'owner_user_id',
        'token_id',
        'name',
        'description',
        'image_url',
        'last_price',
        'rarity',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'last_price' => 'decimal:8',
            'attributes' => 'array',
        ];
    }


    public function nftCollection()
    {
        return $this->belongsTo(NftCollection::class);
    }
}
