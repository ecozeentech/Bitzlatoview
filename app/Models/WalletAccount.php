<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletAccount extends Model
{
    protected $table = 'wallet_accounts';

    protected $fillable = [
        'user_id',
        'type',
        'status',
    ];


    public function balances()
    {
        return $this->hasMany(Balance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
