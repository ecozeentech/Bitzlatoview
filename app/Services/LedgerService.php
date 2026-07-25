<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Balance;
use App\Models\LedgerEntry;
use App\Models\LedgerTransaction;
use App\Models\WalletAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Double-entry ledger service.
 * Never mutate balances directly — all balance changes go through post().
 */
class LedgerService
{
    /**
     * @param  array<int, array{
     *     wallet_account_id:int,
     *     asset_id:int,
     *     entry_type:string,
     *     amount:string|float,
     *     balance_bucket?:string,
     *     metadata?:array<string,mixed>|null
     * }>  $entries
     */
    public function post(
        string $type,
        array $entries,
        ?int $userId = null,
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?int $createdBy = null,
        ?int $approvedBy = null,
        ?string $reason = null,
        ?array $metadata = null,
    ): LedgerTransaction {
        $idempotencyKey ??= (string) Str::uuid();

        $existing = LedgerTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        if (count($entries) < 1) {
            throw new InvalidArgumentException('Ledger requires at least one entry.');
        }

        return DB::transaction(function () use (
            $type, $entries, $userId, $idempotencyKey, $referenceType, $referenceId,
            $description, $createdBy, $approvedBy, $reason, $metadata
        ) {
            $txn = LedgerTransaction::query()->create([
                'uuid' => (string) Str::uuid(),
                'idempotency_key' => $idempotencyKey,
                'type' => $type,
                'status' => 'completed',
                'user_id' => $userId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'created_by' => $createdBy,
                'approved_by' => $approvedBy,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);

            foreach ($entries as $entry) {
                $amount = bcadd((string) $entry['amount'], '0', 8);
                if (bccomp($amount, '0', 8) <= 0) {
                    throw new InvalidArgumentException('Ledger entry amount must be positive.');
                }

                $entryType = strtolower($entry['entry_type']);
                if (! in_array($entryType, ['debit', 'credit'], true)) {
                    throw new InvalidArgumentException('entry_type must be debit or credit.');
                }

                $bucket = $entry['balance_bucket'] ?? 'available';
                if (! in_array($bucket, ['available', 'locked'], true)) {
                    throw new InvalidArgumentException('Invalid balance bucket.');
                }

                $balance = Balance::query()->firstOrCreate(
                    [
                        'wallet_account_id' => $entry['wallet_account_id'],
                        'asset_id' => $entry['asset_id'],
                    ],
                    ['available' => 0, 'locked' => 0]
                );

                $balance = Balance::query()->whereKey($balance->id)->lockForUpdate()->first();

                $current = $bucket === 'available'
                    ? (string) $balance->available
                    : (string) $balance->locked;

                if ($entryType === 'debit') {
                    if (bccomp($current, $amount, 8) < 0) {
                        throw new RuntimeException('Insufficient '.$bucket.' balance for ledger debit.');
                    }
                    $new = bcsub($current, $amount, 8);
                } else {
                    $new = bcadd($current, $amount, 8);
                }

                if ($bucket === 'available') {
                    $balance->available = $new;
                } else {
                    $balance->locked = $new;
                }
                $balance->save();

                LedgerEntry::query()->create([
                    'ledger_transaction_id' => $txn->id,
                    'wallet_account_id' => $entry['wallet_account_id'],
                    'asset_id' => $entry['asset_id'],
                    'entry_type' => $entryType,
                    'balance_bucket' => $bucket,
                    'amount' => $amount,
                    'balance_after' => $new,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'metadata' => $entry['metadata'] ?? null,
                ]);
            }

            return $txn->fresh('entries');
        });
    }

    public function creditAvailable(
        WalletAccount $wallet,
        Asset $asset,
        string|float $amount,
        string $type,
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?int $createdBy = null,
        ?array $metadata = null,
    ): LedgerTransaction {
        return $this->post(
            type: $type,
            entries: [[
                'wallet_account_id' => $wallet->id,
                'asset_id' => $asset->id,
                'entry_type' => 'credit',
                'amount' => $amount,
                'balance_bucket' => 'available',
            ]],
            userId: $wallet->user_id,
            idempotencyKey: $idempotencyKey,
            referenceType: $referenceType,
            referenceId: $referenceId,
            description: $description,
            createdBy: $createdBy,
            metadata: $metadata,
        );
    }

    public function debitAvailable(
        WalletAccount $wallet,
        Asset $asset,
        string|float $amount,
        string $type,
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?int $createdBy = null,
        ?array $metadata = null,
    ): LedgerTransaction {
        return $this->post(
            type: $type,
            entries: [[
                'wallet_account_id' => $wallet->id,
                'asset_id' => $asset->id,
                'entry_type' => 'debit',
                'amount' => $amount,
                'balance_bucket' => 'available',
            ]],
            userId: $wallet->user_id,
            idempotencyKey: $idempotencyKey,
            referenceType: $referenceType,
            referenceId: $referenceId,
            description: $description,
            createdBy: $createdBy,
            metadata: $metadata,
        );
    }

    public function lockFunds(
        WalletAccount $wallet,
        Asset $asset,
        string|float $amount,
        string $type,
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
    ): LedgerTransaction {
        return $this->post(
            type: $type,
            entries: [
                [
                    'wallet_account_id' => $wallet->id,
                    'asset_id' => $asset->id,
                    'entry_type' => 'debit',
                    'amount' => $amount,
                    'balance_bucket' => 'available',
                ],
                [
                    'wallet_account_id' => $wallet->id,
                    'asset_id' => $asset->id,
                    'entry_type' => 'credit',
                    'amount' => $amount,
                    'balance_bucket' => 'locked',
                ],
            ],
            userId: $wallet->user_id,
            idempotencyKey: $idempotencyKey,
            referenceType: $referenceType,
            referenceId: $referenceId,
            description: $description ?? 'Lock funds',
        );
    }

    public function unlockFunds(
        WalletAccount $wallet,
        Asset $asset,
        string|float $amount,
        string $type,
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
    ): LedgerTransaction {
        return $this->post(
            type: $type,
            entries: [
                [
                    'wallet_account_id' => $wallet->id,
                    'asset_id' => $asset->id,
                    'entry_type' => 'debit',
                    'amount' => $amount,
                    'balance_bucket' => 'locked',
                ],
                [
                    'wallet_account_id' => $wallet->id,
                    'asset_id' => $asset->id,
                    'entry_type' => 'credit',
                    'amount' => $amount,
                    'balance_bucket' => 'available',
                ],
            ],
            userId: $wallet->user_id,
            idempotencyKey: $idempotencyKey,
            referenceType: $referenceType,
            referenceId: $referenceId,
            description: $description ?? 'Unlock funds',
        );
    }

    public function transferBetweenWallets(
        WalletAccount $from,
        WalletAccount $to,
        Asset $asset,
        string|float $amount,
        ?string $idempotencyKey = null,
        ?string $description = null,
    ): LedgerTransaction {
        if ($from->user_id !== $to->user_id) {
            throw new InvalidArgumentException('Cross-user wallet transfers are not allowed.');
        }

        return $this->post(
            type: 'transfer',
            entries: [
                [
                    'wallet_account_id' => $from->id,
                    'asset_id' => $asset->id,
                    'entry_type' => 'debit',
                    'amount' => $amount,
                    'balance_bucket' => 'available',
                ],
                [
                    'wallet_account_id' => $to->id,
                    'asset_id' => $asset->id,
                    'entry_type' => 'credit',
                    'amount' => $amount,
                    'balance_bucket' => 'available',
                ],
            ],
            userId: $from->user_id,
            idempotencyKey: $idempotencyKey,
            description: $description ?? "Transfer {$from->type} → {$to->type}",
        );
    }
}
