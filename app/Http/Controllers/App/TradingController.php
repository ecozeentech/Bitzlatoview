<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\MarketPair;
use App\Models\Order;
use App\Models\Trade;
use App\Services\SwapService;
use App\Services\TradingSimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TradingController extends Controller
{
    public function __construct(
        private TradingSimulationService $trading,
        private SwapService $swap,
    ) {}

    public function markets(): View
    {
        return view('app.markets', [
            'pairs' => MarketPair::query()->where('is_active', true)->orderByDesc('volume_24h')->get(),
        ]);
    }

    public function spotIndex(): View
    {
        $pairs = MarketPair::query()->where('is_active', true)->orderBy('symbol')->get();

        return view('app.spot.index', compact('pairs'));
    }

    public function spotShow(Request $request, string $symbol): View
    {
        $pair = MarketPair::query()->where('symbol', strtoupper($symbol))->firstOrFail();
        $user = $request->user();

        return view('app.spot.show', [
            'pair' => $pair,
            'orders' => Order::query()->where('user_id', $user->id)->where('market_pair_id', $pair->id)->latest()->limit(20)->get(),
            'trades' => Trade::query()->where('user_id', $user->id)->where('market_pair_id', $pair->id)->latest()->limit(20)->get(),
            'tradingWallet' => $user->walletAccount('TRADING')?->load('balances.asset'),
        ]);
    }

    public function placeOrder(Request $request, string $symbol): RedirectResponse
    {
        $data = $request->validate([
            'side' => ['required', 'in:buy,sell'],
            'type' => ['required', 'in:market,limit'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'price' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $pair = MarketPair::query()->where('symbol', strtoupper($symbol))->firstOrFail();

        try {
            $order = $this->trading->placeSpotOrder($request->user(), $pair, $data);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Order {$order->status}: {$order->side} {$order->quantity} {$pair->symbol}");
    }

    public function cancelOrder(Request $request, int $id): RedirectResponse
    {
        $order = Order::query()->where('user_id', $request->user()->id)->findOrFail($id);

        try {
            $this->trading->cancelLimitOrder($order);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Order cancelled and funds unlocked.');
    }

    public function buySell(Request $request): View
    {
        return view('app.buy-sell.index', [
            'assets' => Asset::query()->where('type', 'crypto')->where('is_active', true)->get(),
            'pairs' => MarketPair::query()->where('is_active', true)->get(),
        ]);
    }

    public function buySellExecute(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pair_id' => ['required', 'exists:market_pairs,id'],
            'side' => ['required', 'in:buy,sell'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $pair = MarketPair::query()->findOrFail($data['pair_id']);

        try {
            $this->trading->placeSpotOrder($request->user(), $pair, [
                'side' => $data['side'],
                'type' => 'market',
                'quantity' => $data['quantity'],
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Buy/Sell order filled in simulation mode.');
    }

    public function swap(Request $request): View
    {
        return view('app.swap.index', [
            'assets' => Asset::query()->where('type', 'crypto')->where('is_active', true)->get(),
            'wallets' => $request->user()->walletAccounts,
            'history' => \App\Models\SwapTransaction::query()->where('user_id', $request->user()->id)->latest()->limit(20)->get(),
        ]);
    }

    public function swapQuote(Request $request)
    {
        $data = $request->validate([
            'from_asset_id' => ['required', 'exists:assets,id'],
            'to_asset_id' => ['required', 'exists:assets,id', 'different:from_asset_id'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $from = Asset::query()->findOrFail($data['from_asset_id']);
        $to = Asset::query()->findOrFail($data['to_asset_id']);

        return response()->json($this->swap->quote($from, $to, $data['amount']));
    }

    public function swapExecute(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_asset_id' => ['required', 'exists:assets,id'],
            'to_asset_id' => ['required', 'exists:assets,id', 'different:from_asset_id'],
            'from_wallet' => ['required', 'in:PRIMARY,TRADING,INVESTMENT'],
            'to_wallet' => ['required', 'in:PRIMARY,TRADING,INVESTMENT'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $user = $request->user();

        try {
            $this->swap->execute(
                $user,
                $user->walletAccount($data['from_wallet']),
                $user->walletAccount($data['to_wallet']),
                Asset::query()->findOrFail($data['from_asset_id']),
                Asset::query()->findOrFail($data['to_asset_id']),
                $data['amount'],
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Swap executed in simulation mode via ledger.');
    }
}
