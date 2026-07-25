<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Balance;
use App\Models\LedgerEntry;
use App\Models\LedgerTransaction;
use App\Models\User;
use App\Models\WalletAccount;
use App\Support\House;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Central double-entry ledger engine.
 *
 * RULES (per platform compliance requirements):
 *  - Balances are NEVER mutated directly outside this service.
 *  - Every value-moving operation posts a LedgerTransaction with >= 2 LedgerEntry rows.
 *  - For every asset referenced in a transaction, sum(credits) MUST equal sum(debits).
 *    This is what makes the ledger "double entry": debits fund credits.
 *  - CREDIT increases a wallet's available balance, DEBIT decreases it.
 *  - Idempotency keys prevent duplicate postings on retries (e.g. webhook re-delivery).
 *
 * Locking (available <-> locked) does not change total wallet value, so it is handled
 * via lockFunds()/unlockFunds() which atomically move between the two balance buckets
 * under a row lock, without needing a ledger transaction (no value is created/destroyed).
 */
class LedgerService
{
    /**
     * @param  array<int, array{wallet_account_id:int, asset_id:int, direction:string, amount:float|string}>  $entries
     */
    public function post(
        array $entries,
        string $referenceType,
        int|string|null $referenceId = null,
        ?string $description = null,
        ?User $createdBy = null,
        ?User $approvedBy = null,
        ?array $metadata = null,
        ?string $idempotencyKey = null,
    ): LedgerTransaction {
        if (count($entries) < 2) {
            throw new InvalidArgumentException('A ledger transaction requires at least two entries.');
        }

        $idempotencyKey ??= (string) Str::uuid();

        $existing = LedgerTransaction::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        $this->assertBalancedPerAsset($entries);

        return DB::transaction(function () use ($entries, $referenceType, $referenceId, $description, $createdBy, $approvedBy, $metadata, $idempotencyKey) {
            $transaction = LedgerTransaction::create([
                'idempotency_key' => $idempotencyKey,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'created_by' => $createdBy?->id,
                'approved_by' => $approvedBy?->id,
                'metadata' => $metadata,
            ]);

            foreach ($entries as $entry) {
                $balance = $this->lockedBalance($entry['wallet_account_id'], $entry['asset_id']);
                $amount = (string) $entry['amount'];

                if (bccomp($amount, '0', 18) <= 0) {
                    throw new InvalidArgumentException('Ledger entry amounts must be positive.');
                }

                if ($entry['direction'] === LedgerEntry::CREDIT) {
                    $balance->available = bcadd((string) $balance->available, $amount, 18);
                } elseif ($entry['direction'] === LedgerEntry::DEBIT) {
                    $isHouseAccount = $this->isHouseWallet($entry['wallet_account_id']);
                    if (! $isHouseAccount && bccomp((string) $balance->available, $amount, 18) < 0) {
                        throw new RuntimeException('Insufficient available balance for debit.');
                    }
                    $balance->available = bcsub((string) $balance->available, $amount, 18);
                } else {
                    throw new InvalidArgumentException('Invalid ledger direction.');
                }

                $balance->save();

                LedgerEntry::create([
                    'ledger_transaction_id' => $transaction->id,
                    'wallet_account_id' => $entry['wallet_account_id'],
                    'asset_id' => $entry['asset_id'],
                    'direction' => $entry['direction'],
                    'amount' => $amount,
                    'balance_after' => $balance->available,
                ]);
            }

            return $transaction;
        });
    }

    public function lockFunds(WalletAccount $walletAccount, Asset $asset, string $amount): void
    {
        DB::transaction(function () use ($walletAccount, $asset, $amount) {
            $balance = $this->lockedBalance($walletAccount->id, $asset->id);

            if (bccomp((string) $balance->available, $amount, 18) < 0) {
                throw new RuntimeException('Insufficient available balance to lock.');
            }

            $balance->available = bcsub((string) $balance->available, $amount, 18);
            $balance->locked = bcadd((string) $balance->locked, $amount, 18);
            $balance->save();
        });
    }

    public function unlockFunds(WalletAccount $walletAccount, Asset $asset, string $amount): void
    {
        DB::transaction(function () use ($walletAccount, $asset, $amount) {
            $balance = $this->lockedBalance($walletAccount->id, $asset->id);

            if (bccomp((string) $balance->locked, $amount, 18) < 0) {
                throw new RuntimeException('Insufficient locked balance to unlock.');
            }

            $balance->locked = bcsub((string) $balance->locked, $amount, 18);
            $balance->available = bcadd((string) $balance->available, $amount, 18);
            $balance->save();
        });
    }

    /**
     * Release previously locked funds directly into another wallet (e.g. P2P escrow release).
     * This posts a proper ledger transaction: debit the locked pool (asset leaves seller),
     * credit the destination wallet.
     */
    public function releaseLockedFunds(
        WalletAccount $from,
        WalletAccount $to,
        Asset $asset,
        string $amount,
        string $referenceType,
        int|string|null $referenceId = null,
        ?string $description = null,
        ?string $idempotencyKey = null,
    ): LedgerTransaction {
        return DB::transaction(function () use ($from, $to, $asset, $amount, $referenceType, $referenceId, $description, $idempotencyKey) {
            $balance = $this->lockedBalance($from->id, $asset->id);

            if (bccomp((string) $balance->locked, $amount, 18) < 0) {
                throw new RuntimeException('Insufficient locked balance to release.');
            }

            $balance->locked = bcsub((string) $balance->locked, $amount, 18);
            $balance->save();

            LedgerEntry::create([
                'ledger_transaction_id' => null,
                'wallet_account_id' => $from->id,
                'asset_id' => $asset->id,
                'direction' => LedgerEntry::DEBIT,
                'amount' => $amount,
                'balance_after' => $balance->available,
            ]);

            $toBalance = $this->lockedBalance($to->id, $asset->id);
            $toBalance->available = bcadd((string) $toBalance->available, $amount, 18);
            $toBalance->save();

            $transaction = LedgerTransaction::create([
                'idempotency_key' => $idempotencyKey ?? (string) Str::uuid(),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'metadata' => ['type' => 'escrow_release'],
            ]);

            LedgerEntry::where('wallet_account_id', $from->id)
                ->whereNull('ledger_transaction_id')
                ->latest('id')
                ->first()
                ?->update(['ledger_transaction_id' => $transaction->id]);

            LedgerEntry::create([
                'ledger_transaction_id' => $transaction->id,
                'wallet_account_id' => $to->id,
                'asset_id' => $asset->id,
                'direction' => LedgerEntry::CREDIT,
                'amount' => $amount,
                'balance_after' => $toBalance->available,
            ]);

            return $transaction;
        });
    }

    protected ?int $houseUserId = null;

    protected function isHouseWallet(int $walletAccountId): bool
    {
        $this->houseUserId ??= House::user()->id;

        return WalletAccount::where('id', $walletAccountId)->value('user_id') === $this->houseUserId;
    }

    protected function lockedBalance(int $walletAccountId, int $assetId): Balance
    {
        return Balance::query()
            ->where('wallet_account_id', $walletAccountId)
            ->where('asset_id', $assetId)
            ->lockForUpdate()
            ->firstOr(function () use ($walletAccountId, $assetId) {
                return Balance::create([
                    'wallet_account_id' => $walletAccountId,
                    'asset_id' => $assetId,
                    'available' => 0,
                    'locked' => 0,
                ]);
            });
    }

    protected function assertBalancedPerAsset(array $entries): void
    {
        $totals = [];

        foreach ($entries as $entry) {
            $assetId = $entry['asset_id'];
            $totals[$assetId] ??= ['debit' => '0', 'credit' => '0'];
            $totals[$assetId][$entry['direction']] = bcadd($totals[$assetId][$entry['direction']], (string) $entry['amount'], 18);
        }

        foreach ($totals as $assetId => $sides) {
            if (bccomp($sides['debit'], $sides['credit'], 18) !== 0) {
                throw new InvalidArgumentException("Unbalanced ledger entries for asset #{$assetId}: debits {$sides['debit']} != credits {$sides['credit']}.");
            }
        }
    }
}
