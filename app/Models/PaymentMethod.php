<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $guarded = [];

    public const TYPES = ['crypto', 'bank_transfer', 'cashapp', 'venmo', 'paypal', 'other'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
        ];
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function label(): string
    {
        return match ($this->type) {
            'crypto' => $this->network ? "{$this->name} ({$this->network})" : $this->name,
            default => $this->name,
        };
    }
}
