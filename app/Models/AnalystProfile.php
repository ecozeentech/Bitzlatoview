<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalystProfile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['credential_verified' => 'boolean'];
    }

    public function packages(): HasMany
    {
        return $this->hasMany(BillingPackage::class);
    }
}
