<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['allowed_currencies' => 'array'];
    }

    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'max_spending_limit' => 10000,
            'allowed_currencies' => ['USD', 'EUR', 'GBP'],
            'issuance_fee' => 0,
            'funding_fee_pct' => 0,
            'monthly_fee' => 0,
        ]);
    }

    public static function forget(): void
    {
        //
    }
}
