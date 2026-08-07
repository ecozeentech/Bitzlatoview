<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MarketPair;
use App\Models\Order;
use App\Models\Trade;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\SpotMatchingEngine;
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

        // The real order book: other users' resting limit orders, best price first.
        $bids = Order::where('market_pair_id', $market->id)->where('side', 'buy')
            ->whereIn('status', ['new', 'partially_filled'])->whereNotNull('price')
            ->orderByDesc('price')->take(10)->get();
        $asks = Order::where('market_pair_id', $market->id)->where('side', 'sell')
            ->whereIn('status', ['new', 'partially_filled'])->whereNotNull('price')
            ->orderBy('price')->take(10)->get();

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_TRADING]);
        $baseBalance = $wallet->balanceFor($market->baseAsset);
        $quoteBalance = $wallet->balanceFor($market->quoteAsset);

        $markets = MarketPair::with('baseAsset', 'quote')->where('is_active', true)->get();

        return view('app.spot.show', compact('market', 'openOrders', 'orderHistory', 'recentTrades', 'baseBalance', 'quoteBalance', 'markets', 'bids', 'asks'));
    }

    public function store(Request $request, string $symbol, LedgerService $ledger, SpotMatchingEngine $engine)
    {
        $market = MarketPair::where('symbol', $symbol)->with(['baseAsset', 'quoteAsset', 'quote'])->firstOrFail();
        $user = Auth::user();

        $data = $request->validate([
            'side' => ['required', 'in:buy,sell'],
            'type' => ['required', 'in:market,limit'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'price' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $limitPrice = $data['price'] ?? null;

        if ($data['type'] === 'limit' && ! $limitPrice) {
            return back()->with('error', 'Limit price is required for limit orders.');
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_TRADING]);
        $feePct = (float) ($data['type'] === 'market' ? $market->taker_fee_pct : $market->maker_fee_pct) / 100;

        // A market order needs its worst-case cost/quantity available up front since it has
        // no resting price to lock against — check (but don't lock) before matching.
        if ($data['type'] === 'market' && $data['side'] === 'buy') {
            $referencePrice = $this->bestOppositePrice($market, 'buy') ?? (float) ($market->quote->price ?? 0);
            $estimatedCost = $data['quantity'] * $referencePrice * (1 + $feePct);
            if ($wallet->balanceFor($market->quoteAsset)->available < $estimatedCost) {
                return back()->with('error', 'Insufficient balance to place this order.');
            }
        } elseif ($data['type'] === 'market' && $wallet->balanceFor($market->baseAsset)->available < $data['quantity']) {
            return back()->with('error', 'Insufficient balance to place this order.');
        }

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

        $filled = $engine->match($order, $market);
        $order->refresh();
        $remaining = (float) $order->quantity - (float) $order->filled_quantity;

        if ($remaining <= 0) {
            AuditLog::record($user, 'order.filled', Order::class, $order->id);

            return back()->with('success', 'Order fully matched: '.number_format($filled, 8)." {$market->baseAsset->symbol} filled.");
        }

        if ($data['type'] === 'market') {
            if ($filled > 0) {
                return back()->with('success', 'Order partially filled: '.number_format($filled, 8)." {$market->baseAsset->symbol}. No further matching orders were available in the order book.");
            }

            $order->update(['status' => 'rejected']);

            return back()->with('error', 'No matching orders are currently available in the order book for this market order. Try a limit order instead, or a smaller quantity.');
        }

        // Limit order: lock funds for whatever remains unmatched so it can rest on the book.
        try {
            if ($data['side'] === 'buy') {
                $ledger->lockFunds($wallet, $market->quoteAsset, (string) ($remaining * $limitPrice * (1 + $feePct)));
            } else {
                $ledger->lockFunds($wallet, $market->baseAsset, (string) $remaining);
            }
        } catch (\RuntimeException $e) {
            // Unwind any partial fill's worth of order state — the fill already settled
            // through the ledger, so we simply stop here rather than resting the remainder.
            $order->update(['status' => $filled > 0 ? 'partially_filled' : 'rejected']);

            return back()->with($filled > 0 ? 'success' : 'error', $filled > 0
                ? 'Order partially filled: '.number_format($filled, 8)." {$market->baseAsset->symbol}. Remaining quantity could not be locked (insufficient balance) and was not placed on the book."
                : 'Insufficient balance to place this order.');
        }

        AuditLog::record($user, 'order.placed', Order::class, $order->id);

        return back()->with('success', $filled > 0
            ? 'Order partially matched ('.number_format($filled, 8)." {$market->baseAsset->symbol}); the remainder is now open on the order book."
            : 'Limit order placed and is open on the order book.');
    }

    /** Best available opposite-side resting price, used as a worst-case reference for market order balance checks. */
    protected function bestOppositePrice(MarketPair $market, string $side): ?float
    {
        $opposite = $side === 'buy' ? 'sell' : 'buy';
        $order = Order::where('market_pair_id', $market->id)->where('side', $opposite)
            ->whereIn('status', ['new', 'partially_filled'])->whereNotNull('price')
            ->orderBy('price', $side === 'buy' ? 'desc' : 'asc')
            ->first();

        return $order ? (float) $order->price : null;
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
