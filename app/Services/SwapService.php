<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\SwapTransaction;
use App\Models\User;
use App\Models\WalletAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SwapService
{
    public function __construct(private LedgerService $ledger) {}

    public function quote(Asset $from, Asset $to, string|float $amount): array
    {
        $fromPrice = (string) ($from->mock_price_usd ?: '1');
        $toPrice = (string) ($to->mock_price_usd ?: '1');
        $usdValue = bcmul((string) $amount, $fromPrice, 8);
        $fee = bcmul($usdValue, '0.002', 8); // 0.2% fee
        $netUsd = bcsub($usdValue, $fee, 8);
        $toAmount = bcdiv($netUsd, $toPrice, 8);
        $rate = bccomp($fromPrice, '0', 8) > 0 ? bcdiv($toPrice, $fromPrice, 8) : '0';

        return [
            'from_asset' => $from->symbol,
            'to_asset' => $to->symbol,
            'from_amount' => (string) $amount,
            'to_amount' => $toAmount,
            'rate' => $rate,
            'fee_usd' => $fee,
            'slippage' => '0.50',
            'price_impact' => '0.10',
            'minimum_received' => bcmul($toAmount, '0.995', 8),
            'is_simulated' => true,
        ];
    }

    public function execute(
        User $user,
        WalletAccount $fromWallet,
        WalletAccount $toWallet,
        Asset $from,
        Asset $to,
        string|float $amount,
        ?string $idempotencyKey = null,
    ): SwapTransaction {
        if ($fromWallet->user_id !== $user->id || $toWallet->user_id !== $user->id) {
            throw new RuntimeException('Wallet ownership mismatch.');
        }

        $quote = $this->quote($from, $to, $amount);
        $idempotencyKey ??= (string) Str::uuid();

        return DB::transaction(function () use ($user, $fromWallet, $toWallet, $from, $to, $amount, $quote, $idempotencyKey) {
            $swap = SwapTransaction::query()->create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'from_wallet_account_id' => $fromWallet->id,
                'to_wallet_account_id' => $toWallet->id,
                'from_asset_id' => $from->id,
                'to_asset_id' => $to->id,
                'from_amount' => $amount,
                'to_amount' => $quote['to_amount'],
                'rate' => $quote['rate'],
                'fee' => $quote['fee_usd'],
                'slippage' => $quote['slippage'],
                'price_impact' => $quote['price_impact'],
                'status' => 'completed',
                'is_simulated' => true,
            ]);

            $this->ledger->debitAvailable(
                $fromWallet,
                $from,
                $amount,
                'swap',
                $idempotencyKey.'-debit',
                SwapTransaction::class,
                $swap->id,
                'Swap debit '.$from->symbol
            );

            $this->ledger->creditAvailable(
                $toWallet,
                $to,
                $quote['to_amount'],
                'swap',
                $idempotencyKey.'-credit',
                SwapTransaction::class,
                $swap->id,
                'Swap credit '.$to->symbol
            );

            return $swap;
        });
    }
}
