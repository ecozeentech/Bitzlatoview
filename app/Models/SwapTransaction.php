<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwapTransaction extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class);
    }

    public function fromAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'from_asset_id');
    }

    public function toAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'to_asset_id');
    }
}
