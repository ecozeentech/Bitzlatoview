<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiBot extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['supported_assets' => 'array'];
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(AiBotAllocation::class);
    }

    public function performance(): HasMany
    {
        return $this->hasMany(AiBotPerformance::class);
    }
}
