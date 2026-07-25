<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2PMerchantProfile extends Model
{
    protected $table = 'p2p_merchant_profiles';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
