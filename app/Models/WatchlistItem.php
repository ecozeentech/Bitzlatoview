<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WatchlistItem extends Model
{
    protected $table = 'watchlist_items';

    protected $fillable = [
        'user_id',
        'watchable_type',
        'watchable_id',
        'symbol',
    ];

}
