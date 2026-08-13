<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SignalPackage extends Model
{
    protected $guarded = [];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SignalSubscription::class);
    }
}
