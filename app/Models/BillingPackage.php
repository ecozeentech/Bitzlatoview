<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPackage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['features' => 'array', 'report_access' => 'boolean'];
    }

    public function analyst(): BelongsTo
    {
        return $this->belongsTo(AnalystProfile::class, 'analyst_profile_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'billing_package_id');
    }
}
