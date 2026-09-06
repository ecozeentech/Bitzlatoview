<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    protected $guarded = [];

    /**
     * The admin-configurable withdrawal fee, carved directly out of the requested amount
     * (see admin/settings/withdrawal-fee). Clamped so it can never exceed the amount itself.
     */
    public static function calculateFee(float $amount): float
    {
        if (! SystemSetting::getValue('withdrawal_fee_enabled', true)) {
            return 0.0;
        }

        $pct = (float) SystemSetting::getValue('withdrawal_fee_percentage', 0.1);
        $fee = round($amount * ($pct / 100), 8);

        $min = SystemSetting::getValue('withdrawal_fee_min');
        if ($min !== null && $fee < (float) $min) {
            $fee = (float) $min;
        }

        $max = SystemSetting::getValue('withdrawal_fee_max');
        if ($max !== null && $fee > (float) $max) {
            $fee = (float) $max;
        }

        return min($fee, $amount);
    }

    protected function casts(): array
    {
        return ['metadata' => 'array', 'approved_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
