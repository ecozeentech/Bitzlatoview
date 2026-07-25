<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\MarketPair;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use App\Models\WalletAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TradingSimulationService
{
    public function __construct(private LedgerService $ledger) {}

    public function placeSpotOrder(User $user, MarketPair $pair, array $data): Order
    {
        $trading = $user->walletAccount('TRADING');
        if (! $trading) {
            throw new RuntimeException('Trading wallet not found.');
        }

        $side = $data['side'];
        $type = $data['type'] ?? 'market';
        $quantity = (string) $data['quantity'];
        $price = isset($data['price']) ? (string) $data['price'] : (string) $pair->last_price;

        return DB::transaction(function () use ($user, $pair, $trading, $side, $type, $quantity, $price, $data) {
            $order = Order::query()->create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'market_pair_id' => $pair->id,
                'side' => $side,
                'type' => $type,
                'status' => 'new',
                'price' => $type === 'limit' ? $price : null,
                'quantity' => $quantity,
                'filled_quantity' => 0,
                'is_simulated' => true,
            ]);

            if ($type === 'limit') {
                $this->lockForLimitOrder($trading, $pair, $side, $quantity, $price, $order);

                return $order;
            }

            return $this->fillMarketOrder($order, $pair, $trading, $side, $quantity, $price);
        });
    }

    private function lockForLimitOrder(
        WalletAccount $trading,
        MarketPair $pair,
        string $side,
        string $quantity,
        string $price,
        Order $order
    ): void {
        if ($side === 'buy') {
            $quote = Asset::query()->findOrFail($pair->quote_asset_id);
            $notional = bcmul($quantity, $price, 8);
            $this->ledger->lockFunds(
                $trading,
                $quote,
                $notional,
                'order_lock',
                'order-lock-'.$order->uuid,
                Order::class,
                $order->id,
                'Lock quote for limit buy'
            );
        } else {
            $base = Asset::query()->findOrFail($pair->base_asset_id);
            $this->ledger->lockFunds(
                $trading,
                $base,
                $quantity,
                'order_lock',
                'order-lock-'.$order->uuid,
                Order::class,
                $order->id,
                'Lock base for limit sell'
            );
        }
    }

    private function fillMarketOrder(
        Order $order,
        MarketPair $pair,
        WalletAccount $trading,
        string $side,
        string $quantity,
        string $price
    ): Order {
        $base = Asset::query()->findOrFail($pair->base_asset_id);
        $quote = Asset::query()->findOrFail($pair->quote_asset_id);
        $notional = bcmul($quantity, $price, 8);
        $fee = bcmul($notional, '0.001', 8); // 0.1% simulated fee

        if ($side === 'buy') {
            $totalDebit = bcadd($notional, $fee, 8);
            $this->ledger->debitAvailable(
                $trading,
                $quote,
                $totalDebit,
                'trade',
                'trade-debit-'.$order->uuid,
                Order::class,
                $order->id,
                'Market buy debit'
            );
            $this->ledger->creditAvailable(
                $trading,
                $base,
                $quantity,
                'trade',
                'trade-credit-'.$order->uuid,
                Order::class,
                $order->id,
                'Market buy credit'
            );
        } else {
            $this->ledger->debitAvailable(
                $trading,
                $base,
                $quantity,
                'trade',
                'trade-debit-'.$order->uuid,
                Order::class,
                $order->id,
                'Market sell debit'
            );
            $net = bcsub($notional, $fee, 8);
            $this->ledger->creditAvailable(
                $trading,
                $quote,
                $net,
                'trade',
                'trade-credit-'.$order->uuid,
                Order::class,
                $order->id,
                'Market sell credit'
            );
        }

        Trade::query()->create([
            'uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'market_pair_id' => $pair->id,
            'side' => $side,
            'price' => $price,
            'quantity' => $quantity,
            'fee' => $fee,
            'fee_asset' => $quote->symbol,
            'is_simulated' => true,
        ]);

        $order->update([
            'status' => 'filled',
            'filled_quantity' => $quantity,
            'avg_fill_price' => $price,
            'fee' => $fee,
            'filled_at' => now(),
        ]);

        return $order->fresh();
    }

    public function cancelLimitOrder(Order $order): Order
    {
        if ($order->status !== 'new' || $order->type !== 'limit') {
            throw new RuntimeException('Only open limit orders can be cancelled.');
        }

        $pair = MarketPair::query()->findOrFail($order->market_pair_id);
        $trading = WalletAccount::query()->where('user_id', $order->user_id)->where('type', 'TRADING')->firstOrFail();

        if ($order->side === 'buy') {
            $quote = Asset::query()->findOrFail($pair->quote_asset_id);
            $notional = bcmul((string) $order->quantity, (string) $order->price, 8);
            $this->ledger->unlockFunds($trading, $quote, $notional, 'order_unlock', 'order-unlock-'.$order->uuid, Order::class, $order->id);
        } else {
            $base = Asset::query()->findOrFail($pair->base_asset_id);
            $this->ledger->unlockFunds($trading, $base, (string) $order->quantity, 'order_unlock', 'order-unlock-'.$order->uuid, Order::class, $order->id);
        }

        $order->update(['status' => 'cancelled']);

        return $order->fresh();
    }
}
