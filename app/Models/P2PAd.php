<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2PAd extends Model
{
    protected $table = 'p2p_ads';

    protected $fillable = [
        'user_id',
        'merchant_profile_id',
        'asset_id',
        'side',
        'fiat_currency',
        'price_type',
        'price',
        'available_amount',
        'min_limit',
        'max_limit',
        'payment_method_ids',
        'terms',
        'is_visible',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'available_amount' => 'decimal:8',
            'min_limit' => 'decimal:2',
            'max_limit' => 'decimal:2',
            'payment_method_ids' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function merchantProfile()
    {
        return $this->belongsTo(P2PMerchantProfile::class, 'merchant_profile_id');
    }
}
