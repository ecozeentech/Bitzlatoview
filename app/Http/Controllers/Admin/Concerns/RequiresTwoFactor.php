<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;

/**
 * Step-up verification for the platform's most sensitive admin actions (unlocking/editing
 * locked balances, manually approving KYC without documents). If the acting admin has 2FA
 * enabled on their account, a valid current TOTP code must be submitted along with the
 * action. If they haven't enabled 2FA at all, we can't require a code that doesn't exist —
 * the action proceeds, but every use of this trait's actions is already fully audit-logged
 * (see the AuditLog::record() calls in each controller) so who-did-what-when is never lost.
 */
trait RequiresTwoFactor
{
    protected function verifyTwoFactor(Request $request, User $admin): ?string
    {
        if (! $admin->two_factor_enabled) {
            return null;
        }

        $code = trim((string) $request->input('totp_code'));

        if ($code === '') {
            return 'This action requires your 2FA code since you have two-factor authentication enabled.';
        }

        if (! app(TotpService::class)->verify($admin->two_factor_secret, $code)) {
            return 'Invalid 2FA code. Please try again.';
        }

        return null;
    }
}
