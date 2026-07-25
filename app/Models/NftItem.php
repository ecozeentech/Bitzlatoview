<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NftItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['rarity' => 'array', 'is_listed' => 'boolean'];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(NftCollection::class, 'nft_collection_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(NftBid::class, 'nft_item_id')->latest();
    }
}
