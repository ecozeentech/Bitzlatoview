<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MarketPair;
use App\Models\Order;
use App\Models\Trade;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpotController extends Controller
{
    public function index()
    {
        $markets = MarketPair::with(['baseAsset', 'quote'])->where('is_active', true)->get();

        return redirect('/app/spot/'.($markets->first()->symbol ?? 'BTC-USDT'));
    }

    public function show(string $symbol)
    {
        $market = MarketPair::where('symbol', $symbol)->with(['baseAsset', 'quoteAsset', 'quote'])->firstOrFail();
        $user = Auth::user();

        $openOrders = Order::where('user_id', $user->id)->where('market_pair_id', $market->id)
            ->whereIn('status', ['new', 'partially_filled'])->latest()->get();

        $orderHistory = Order::where('user_id', $user->id)->where('market_pair_id', $market->id)
            ->whereNotIn('status', ['new', 'partially_filled'])->latest()->take(15)->get();

        $recentTrades = Trade::whereHas('order', fn ($q) => $q->where('market_pair_id', $market->id))
            ->latest()->take(10)->get();

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_TRADING]);
        $baseBalance = $wallet->balanceFor($market->baseAsset);
        $quoteBalance = $wallet->balanceFor($market->quoteAsset);

        $markets = MarketPair::with('baseAsset', 'quote')->where('is_active', true)->get();

        return view('app.spot.show', compact('market', 'openOrders', 'orderHistory', 'recentTrades', 'baseBalance', 'quoteBalance', 'markets'));
    }

    public function store(Request $request, string $symbol, LedgerService $ledger)
    {
        $market = MarketPair::where('symbol', $symbol)->with(['baseAsset', 'quoteAsset', 'quote'])->firstOrFail();
        $user = Auth::user();

        $data = $request->validate([
            'side' => ['required', 'in:buy,sell'],
            'type' => ['required', 'in:market,limit'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'price' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $currentPrice = (float) ($market->quote->price ?? 0);
        $limitPrice = $data['price'] ?? null;

        if ($data['type'] === 'limit' && ! $limitPrice) {
            return back()->with('error', 'Limit price is required for limit orders.');
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_TRADING]);

        $order = Order::create([
            'user_id' => $user->id,
            'market_pair_id' => $market->id,
            'wallet_account_id' => $wallet->id,
            'side' => $data['side'],
            'type' => $data['type'],
            'price' => $limitPrice,
            'quantity' => $data['quantity'],
            'status' => 'new',
        ]);

        $willFillNow = $data['type'] === 'market'
            || ($data['side'] === 'buy' && $limitPrice >= $currentPrice)
            || ($data['side'] === 'sell' && $limitPrice <= $currentPrice);

        $fillPrice = $data['type'] === 'market' ? $currentPrice : $limitPrice;
        $feePct = (float) ($data['type'] === 'market' ? $market->taker_fee_pct : $market->maker_fee_pct) / 100;

        if (! $willFillNow) {
            try {
                if ($data['side'] === 'buy') {
                    $ledger->lockFunds($wallet, $market->quoteAsset, (string) ($data['quantity'] * $limitPrice * (1 + $feePct)));
                } else {
                    $ledger->lockFunds($wallet, $market->baseAsset, (string) $data['quantity']);
                }
            } catch (\RuntimeException $e) {
                $order->update(['status' => 'rejected']);

                return back()->with('error', 'Insufficient balance to place this order.');
            }

            AuditLog::record($user, 'order.placed', Order::class, $order->id);

            return back()->with('success', 'Limit order placed and is open.');
        }

        $this->fill($order, $market, $fillPrice, $data['quantity'], $feePct, $ledger, $user);

        return back()->with('success', 'Order filled.');
    }

    protected function fill(Order $order, MarketPair $market, float $price, float $quantity, float $feePct, LedgerService $ledger, $user): void
    {
        $wallet = $order->walletAccount;
        $house = House::wallet(WalletAccount::TYPE_TRADING);

        $quoteAmount = $quantity * $price;
        $fee = $quoteAmount * $feePct;

        if ($order->side === 'buy') {
            $totalCost = $quoteAmount + $fee;
            try {
                $ledger->post(
                    entries: [
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $market->quote_asset_id, 'direction' => 'debit', 'amount' => $totalCost],
                        ['wallet_account_id' => $house->id, 'asset_id' => $market->quote_asset_id, 'direction' => 'credit', 'amount' => $totalCost],
                        ['wallet_account_id' => $house->id, 'asset_id' => $market->base_asset_id, 'direction' => 'debit', 'amount' => $quantity],
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $market->base_asset_id, 'direction' => 'credit', 'amount' => $quantity],
                    ],
                    referenceType: 'order',
                    referenceId: $order->id,
                    description: "Buy {$quantity} {$market->baseAsset->symbol} @ {$price}",
                    createdBy: $user,
                );
            } catch (\RuntimeException $e) {
                $order->update(['status' => 'rejected']);

                return;
            }
        } else {
            $proceeds = $quoteAmount - $fee;
            try {
                $ledger->post(
                    entries: [
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $market->base_asset_id, 'direction' => 'debit', 'amount' => $quantity],
                        ['wallet_account_id' => $house->id, 'asset_id' => $market->base_asset_id, 'direction' => 'credit', 'amount' => $quantity],
                        ['wallet_account_id' => $house->id, 'asset_id' => $market->quote_asset_id, 'direction' => 'debit', 'amount' => $proceeds],
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $market->quote_asset_id, 'direction' => 'credit', 'amount' => $proceeds],
                    ],
                    referenceType: 'order',
                    referenceId: $order->id,
                    description: "Sell {$quantity} {$market->baseAsset->symbol} @ {$price}",
                    createdBy: $user,
                );
            } catch (\RuntimeException $e) {
                $order->update(['status' => 'rejected']);

                return;
            }
        }

        Trade::create([
            'order_id' => $order->id,
            'price' => $price,
            'quantity' => $quantity,
            'fee' => $fee,
        ]);

        $order->update(['filled_quantity' => $quantity, 'status' => 'filled']);

        AuditLog::record($user, 'order.filled', Order::class, $order->id);
    }

    public function cancel(Order $order, LedgerService $ledger)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if (! in_array($order->status, ['new', 'partially_filled'])) {
            return back()->with('error', 'Only open orders can be cancelled.');
        }

        $market = $order->marketPair;
        $remaining = $order->quantity - $order->filled_quantity;

        if ($order->side === 'buy') {
            $feePct = (float) $market->maker_fee_pct / 100;
            $ledger->unlockFunds($order->walletAccount, $market->quoteAsset, (string) ($remaining * $order->price * (1 + $feePct)));
        } else {
            $ledger->unlockFunds($order->walletAccount, $market->baseAsset, (string) $remaining);
        }

        $order->update(['status' => 'cancelled']);
        AuditLog::record(Auth::user(), 'order.cancelled', Order::class, $order->id);

        return back()->with('success', 'Order cancelled and funds released.');
    }
}
