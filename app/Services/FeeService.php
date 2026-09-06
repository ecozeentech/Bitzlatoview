<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\LedgerTransaction;
use App\Models\User;
use App\Models\WalletAccount;
use App\Support\House;
use RuntimeException;

/**
 * Central place every platform fee (trading, swap, mining maintenance, signal performance,
 * card issuance/funding, etc.) is charged from — always the user's Primary Wallet, never
 * Trading or Investment, per platform policy. Fee revenue always lands in the House's
 * Primary Wallet.
 *
 * Withdrawal fees are the one exception: per the withdrawal flow's own design, that fee is
 * carved directly out of the withdrawal amount itself (see FundingController), not charged
 * as a separate Primary Wallet debit — so it is not routed through this service.
 */
class FeeService
{
    public function __construct(protected LedgerService $ledger) {}

    /**
     * Charge a fee from the user's Primary Wallet. Throws RuntimeException (via the ledger)
     * if the Primary Wallet's available balance in $asset can't cover it — callers decide
     * whether that should block the parent action or just be logged and skipped.
     */
    public function charge(
        User $user,
        Asset $asset,
        float|string $amount,
        string $referenceType,
        int|string|null $referenceId = null,
        ?string $description = null,
    ): ?LedgerTransaction {
        $amount = $this->normalizeAmount($amount);

        if (bccomp($amount, '0', 18) <= 0) {
            return null;
        }

        $primary = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);

        if ($primary->is_suspended) {
            throw new RuntimeException('Primary wallet is suspended; platform fees cannot be charged.');
        }

        $house = House::wallet(WalletAccount::TYPE_PRIMARY);

        return $this->ledger->post(
            entries: [
                ['wallet_account_id' => $primary->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
            ],
            referenceType: $referenceType,
            referenceId: $referenceId,
            description: $description ?? 'Platform fee',
        );
    }

    public function hasSufficientPrimaryBalance(User $user, Asset $asset, float|string $amount): bool
    {
        $primary = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);

        if ($primary->is_suspended) {
            return false;
        }

        $balance = $primary->balanceFor($asset);

        return bccomp((string) $balance->available, $this->normalizeAmount($amount), 18) >= 0;
    }

    /**
     * PHP's (string) cast on a float can produce scientific notation (e.g. "1.0E-5") for very
     * small amounts, which bcmath functions reject outright — normalize to a plain decimal
     * string first so tiny fee amounts (common once fees are computed as a % of a % of a
     * small trade) never blow up bccomp()/bcadd()/etc.
     */
    protected function normalizeAmount(float|string $amount): string
    {
        return is_float($amount) ? number_format($amount, 18, '.', '') : $amount;
    }

    /**
     * For fee points that settle automatically (mining accrual, signal/AI/copy PnL close-out,
     * spot fills) where blocking the underlying action over an unrelated fee shortfall would
     * be worse than just not collecting that one fee. Returns true if the fee was charged.
     */
    public function attemptCharge(
        User $user,
        Asset $asset,
        string $amount,
        string $referenceType,
        int|string|null $referenceId = null,
        ?string $description = null,
    ): bool {
        try {
            $this->charge($user, $asset, $amount, $referenceType, $referenceId, $description);

            return true;
        } catch (RuntimeException $e) {
            AuditLog::record(null, 'fee.waived_insufficient_primary_balance', $referenceType, $referenceId, null, [
                'user_id' => $user->id,
                'asset' => $asset->symbol,
                'amount' => $amount,
                'reason' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
