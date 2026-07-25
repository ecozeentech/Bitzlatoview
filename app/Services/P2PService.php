<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\FundingNote;
use App\Models\P2PAd;
use App\Models\P2PAppeal;
use App\Models\P2PEscrow;
use App\Models\P2POrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class P2PService
{
    public function __construct(private LedgerService $ledger) {}

    public function createOrder(User $buyer, P2PAd $ad, string|float $fiatAmount, ?string $paymentMethod = null, ?string $userNote = null): P2POrder
    {
        if ($ad->side !== 'sell') {
            throw new RuntimeException('Only seller ads are supported for buy flow in MVP.');
        }
        if ($buyer->id === $ad->user_id) {
            throw new RuntimeException('Cannot trade your own ad.');
        }

        $fiat = (string) $fiatAmount;
        $price = (string) $ad->price;
        $cryptoAmount = bcdiv($fiat, $price, 8);

        if (bccomp($fiat, (string) $ad->min_limit, 2) < 0 || bccomp($fiat, (string) $ad->max_limit, 2) > 0) {
            throw new RuntimeException('Amount outside ad limits.');
        }
        if (bccomp($cryptoAmount, (string) $ad->available_amount, 8) > 0) {
            throw new RuntimeException('Insufficient ad liquidity.');
        }

        return DB::transaction(function () use ($buyer, $ad, $fiat, $cryptoAmount, $price, $paymentMethod, $userNote) {
            $sellerPrimary = User::query()->findOrFail($ad->user_id)->walletAccount('PRIMARY');
            $asset = Asset::query()->findOrFail($ad->asset_id);

            $order = P2POrder::query()->create([
                'uuid' => (string) Str::uuid(),
                'ad_id' => $ad->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $ad->user_id,
                'asset_id' => $ad->asset_id,
                'fiat_currency' => $ad->fiat_currency,
                'crypto_amount' => $cryptoAmount,
                'fiat_amount' => $fiat,
                'price' => $price,
                'payment_method' => $paymentMethod,
                'status' => 'escrow_locked',
                'payment_deadline' => now()->addMinutes(15),
                'is_simulated' => true,
            ]);

            $this->ledger->lockFunds(
                $sellerPrimary,
                $asset,
                $cryptoAmount,
                'p2p_escrow',
                'p2p-escrow-'.$order->uuid,
                P2POrder::class,
                $order->id,
                'P2P escrow lock'
            );

            P2PEscrow::query()->create([
                'p2p_order_id' => $order->id,
                'wallet_account_id' => $sellerPrimary->id,
                'asset_id' => $asset->id,
                'amount' => $cryptoAmount,
                'status' => 'locked',
            ]);

            $ad->decrement('available_amount', $cryptoAmount);
            $order->update(['status' => 'awaiting_payment']);

            if ($userNote) {
                FundingNote::query()->create([
                    'notable_type' => P2POrder::class,
                    'notable_id' => $order->id,
                    'user_note' => $userNote,
                ]);
            }

            return $order->fresh();
        });
    }

    public function markPaid(P2POrder $order, User $actor): P2POrder
    {
        if ($actor->id !== $order->buyer_id) {
            throw new RuntimeException('Only buyer can mark paid.');
        }
        if ($order->status !== 'awaiting_payment') {
            throw new RuntimeException('Invalid order status.');
        }

        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $order->fresh();
    }

    public function release(P2POrder $order, User $actor): P2POrder
    {
        if ($actor->id !== $order->seller_id && ! $actor->isAdmin()) {
            throw new RuntimeException('Only seller or admin can release.');
        }
        if (! in_array($order->status, ['paid', 'appealed'], true)) {
            throw new RuntimeException('Invalid order status for release.');
        }

        return DB::transaction(function () use ($order) {
            $escrow = P2PEscrow::query()->where('p2p_order_id', $order->id)->firstOrFail();
            $sellerWallet = $escrow->wallet_account_id;
            $sellerAccount = \App\Models\WalletAccount::query()->findOrFail($sellerWallet);
            $buyerPrimary = User::query()->findOrFail($order->buyer_id)->walletAccount('PRIMARY');
            $asset = Asset::query()->findOrFail($order->asset_id);

            // Unlock from seller locked, then move to buyer available via debit locked + credit buyer
            $this->ledger->post(
                type: 'p2p_release',
                entries: [
                    [
                        'wallet_account_id' => $sellerAccount->id,
                        'asset_id' => $asset->id,
                        'entry_type' => 'debit',
                        'amount' => (string) $order->crypto_amount,
                        'balance_bucket' => 'locked',
                    ],
                    [
                        'wallet_account_id' => $buyerPrimary->id,
                        'asset_id' => $asset->id,
                        'entry_type' => 'credit',
                        'amount' => (string) $order->crypto_amount,
                        'balance_bucket' => 'available',
                    ],
                ],
                userId: $order->buyer_id,
                idempotencyKey: 'p2p-release-'.$order->uuid,
                referenceType: P2POrder::class,
                referenceId: $order->id,
                description: 'P2P escrow release to buyer',
            );

            $escrow->update(['status' => 'released']);
            $order->update([
                'status' => 'completed',
                'released_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    public function cancel(P2POrder $order, User $actor): P2POrder
    {
        if (! in_array($actor->id, [$order->buyer_id, $order->seller_id], true) && ! $actor->isAdmin()) {
            throw new RuntimeException('Not authorized.');
        }
        if (! in_array($order->status, ['created', 'escrow_locked', 'awaiting_payment'], true)) {
            throw new RuntimeException('Order cannot be cancelled.');
        }

        return DB::transaction(function () use ($order) {
            $escrow = P2PEscrow::query()->where('p2p_order_id', $order->id)->first();
            if ($escrow && $escrow->status === 'locked') {
                $sellerAccount = \App\Models\WalletAccount::query()->findOrFail($escrow->wallet_account_id);
                $asset = Asset::query()->findOrFail($order->asset_id);
                $this->ledger->unlockFunds(
                    $sellerAccount,
                    $asset,
                    (string) $order->crypto_amount,
                    'p2p_cancel',
                    'p2p-cancel-'.$order->uuid,
                    P2POrder::class,
                    $order->id,
                    'P2P cancel unlock'
                );
                $escrow->update(['status' => 'cancelled']);
                P2PAd::query()->whereKey($order->ad_id)->increment('available_amount', $order->crypto_amount);
            }

            $order->update(['status' => 'cancelled']);

            return $order->fresh();
        });
    }

    public function openAppeal(P2POrder $order, User $actor, string $reason, ?string $evidenceUrl = null): P2PAppeal
    {
        if (! in_array($actor->id, [$order->buyer_id, $order->seller_id], true)) {
            throw new RuntimeException('Not authorized.');
        }

        $order->update(['status' => 'appealed']);

        return P2PAppeal::query()->create([
            'p2p_order_id' => $order->id,
            'opened_by' => $actor->id,
            'reason' => $reason,
            'evidence_url' => $evidenceUrl,
            'status' => 'open',
        ]);
    }
}
