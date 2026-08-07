<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\MarketPair;
use App\Models\Order;
use App\Models\Trade;
use App\Models\WalletAccount;
use App\Support\House;
use Illuminate\Support\Facades\DB;

/**
 * A genuine peer-to-peer order book matching engine: incoming orders are matched directly
 * against other users' resting orders in the same market, price-then-time priority (FIFO),
 * with settlement between the two real users' wallets through the ledger. There is no
 * "house" counterparty standing in as fake liquidity here — if there is nobody on the other
 * side of the book, the order simply does not fill (or only partially fills), exactly like a
 * real exchange with no market maker connected yet.
 */
class SpotMatchingEngine
{
    public function __construct(protected LedgerService $ledger) {}

    /**
     * Attempt to match $incoming against the resting book. Returns the quantity filled.
     */
    public function match(Order $incoming, MarketPair $market): float
    {
        $filled = 0.0;
        $remaining = (float) $incoming->quantity - (float) $incoming->filled_quantity;

        if ($remaining <= 0) {
            return 0.0;
        }

        $oppositeSide = $incoming->side === 'buy' ? 'sell' : 'buy';

        $bookQuery = Order::where('market_pair_id', $market->id)
            ->where('side', $oppositeSide)
            ->whereIn('status', ['new', 'partially_filled'])
            ->where('user_id', '!=', $incoming->user_id)
            ->whereNotNull('price');

        // Price priority: best price for the incoming order first, then oldest order (FIFO).
        $bookQuery = $incoming->side === 'buy'
            ? $bookQuery->orderBy('price', 'asc')
            : $bookQuery->orderBy('price', 'desc');

        $restingOrders = $bookQuery->orderBy('created_at', 'asc')->lockForUpdate()->get();

        foreach ($restingOrders as $resting) {
            if ($remaining <= 0) {
                break;
            }

            $crosses = $incoming->type === 'market'
                || ($incoming->side === 'buy' && $incoming->price >= $resting->price)
                || ($incoming->side === 'sell' && $incoming->price <= $resting->price);

            if (! $crosses) {
                // Book is price-sorted, so once one resting order no longer crosses, none after it will either.
                break;
            }

            $restingRemaining = (float) $resting->quantity - (float) $resting->filled_quantity;
            $matchQty = min($remaining, $restingRemaining);

            if ($matchQty <= 0) {
                continue;
            }

            $executed = $this->executeTrade($incoming, $resting, $market, $matchQty, (float) $resting->price);

            if ($executed) {
                $filled += $matchQty;
                $remaining -= $matchQty;
            }
        }

        return $filled;
    }

    protected function executeTrade(Order $taker, Order $maker, MarketPair $market, float $quantity, float $price): bool
    {
        return DB::transaction(function () use ($taker, $maker, $market, $quantity, $price) {
            $buyOrder = $taker->side === 'buy' ? $taker : $maker;
            $sellOrder = $taker->side === 'buy' ? $maker : $taker;
            $takerFeePct = (float) $market->taker_fee_pct / 100;
            $makerFeePct = (float) $market->maker_fee_pct / 100;

            $quoteAmount = $quantity * $price;
            $buyerFeePct = $buyOrder->id === $taker->id ? $takerFeePct : $makerFeePct;
            $sellerFeePct = $sellOrder->id === $taker->id ? $takerFeePct : $makerFeePct;
            $buyerFee = round($quoteAmount * $buyerFeePct, 18);
            $sellerFee = round($quoteAmount * $sellerFeePct, 18);
            $feeRevenue = House::wallet(WalletAccount::TYPE_TRADING);

            // The resting (maker) side has its funds sitting in `locked`, not `available`,
            // since they were reserved when the order was originally placed. Release exactly
            // the matched portion back to `available` first so the ledger post below (which
            // always debits from `available`) has something to debit.
            try {
                if ($maker->side === 'buy') {
                    $this->ledger->unlockFunds($maker->walletAccount, $market->quoteAsset, (string) ($quoteAmount + $buyerFee));
                } else {
                    $this->ledger->unlockFunds($maker->walletAccount, $market->baseAsset, (string) $quantity);
                }
            } catch (\RuntimeException $e) {
                return false;
            }

            try {
                // Buyer pays quoteAmount + their fee; seller receives quoteAmount minus their
                // fee; both fees flow to the platform's trading fee revenue account. Base
                // asset simply moves seller -> buyer. Every leg is a real transfer between
                // real users (or the platform's own disclosed fee account) — no phantom
                // "house" counterparty standing in as fake liquidity.
                $this->ledger->post(
                    entries: [
                        ['wallet_account_id' => $buyOrder->wallet_account_id, 'asset_id' => $market->quote_asset_id, 'direction' => 'debit', 'amount' => $quoteAmount + $buyerFee],
                        ['wallet_account_id' => $sellOrder->wallet_account_id, 'asset_id' => $market->quote_asset_id, 'direction' => 'credit', 'amount' => $quoteAmount - $sellerFee],
                        ['wallet_account_id' => $feeRevenue->id, 'asset_id' => $market->quote_asset_id, 'direction' => 'credit', 'amount' => $buyerFee + $sellerFee],
                        ['wallet_account_id' => $sellOrder->wallet_account_id, 'asset_id' => $market->base_asset_id, 'direction' => 'debit', 'amount' => $quantity],
                        ['wallet_account_id' => $buyOrder->wallet_account_id, 'asset_id' => $market->base_asset_id, 'direction' => 'credit', 'amount' => $quantity],
                    ],
                    referenceType: 'p2p_order_match',
                    referenceId: $taker->id,
                    description: "Matched {$quantity} {$market->baseAsset->symbol} @ {$price} between order #{$buyOrder->id} and #{$sellOrder->id}",
                );
            } catch (\RuntimeException $e) {
                // The resting order's locked funds should always cover this, but guard anyway.
                return false;
            }

            Trade::create([
                'order_id' => $taker->id,
                'price' => $price,
                'quantity' => $quantity,
                'fee' => $takerFeePct === $buyerFeePct ? $buyerFee : $sellerFee,
            ]);
            Trade::create([
                'order_id' => $maker->id,
                'price' => $price,
                'quantity' => $quantity,
                'fee' => $takerFeePct === $buyerFeePct ? $sellerFee : $buyerFee,
            ]);

            foreach ([$taker, $maker] as $order) {
                $order->refresh();
                $newFilled = (float) $order->filled_quantity + $quantity;
                $order->update([
                    'filled_quantity' => $newFilled,
                    'status' => $newFilled >= (float) $order->quantity ? 'filled' : 'partially_filled',
                ]);
            }

            AuditLog::record(null, 'order.matched', Order::class, $taker->id, null, [
                'maker_order_id' => $maker->id, 'quantity' => $quantity, 'price' => $price,
            ]);

            return true;
        });
    }
}
