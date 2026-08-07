<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\FuturesMarket;
use App\Models\FuturesPosition;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\PricingService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FuturesController extends Controller
{
    public function index()
    {
        $markets = FuturesMarket::with('asset')->get();
        $positions = FuturesPosition::where('user_id', Auth::id())->where('status', 'open')->with('market.asset')->get();
        $history = FuturesPosition::where('user_id', Auth::id())->where('status', '!=', 'open')->with('market.asset')->latest()->take(15)->get();

        return view('app.futures.index', compact('markets', 'positions', 'history'));
    }

    public function store(Request $request, FuturesMarket $market, LedgerService $ledger, PricingService $pricing)
    {
        $user = Auth::user();

        $data = $request->validate([
            'side' => ['required', 'in:long,short'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'leverage' => ['required', 'integer', 'min:1', 'max:'.$market->max_leverage],
            'margin_mode' => ['required', 'in:cross,isolated'],
        ]);

        $entryPrice = $pricing->usdPrice($market->asset) ?: (float) $market->mark_price;
        $market->update(['mark_price' => $entryPrice, 'index_price' => $entryPrice]);
        $notional = $entryPrice * $data['quantity'];
        $margin = round($notional / $data['leverage'], 2);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_TRADING]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();

        try {
            $ledger->lockFunds($wallet, $usdt, (string) $margin);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient margin available in your Trading Wallet.');
        }

        $maintenanceMargin = $entryPrice * ((float) $market->maintenance_margin_pct / 100);
        $liquidationPrice = $data['side'] === 'long'
            ? $entryPrice - ($margin / $data['quantity']) + $maintenanceMargin
            : $entryPrice + ($margin / $data['quantity']) - $maintenanceMargin;

        $position = FuturesPosition::create([
            'user_id' => $user->id,
            'futures_market_id' => $market->id,
            'side' => $data['side'],
            'leverage' => $data['leverage'],
            'margin_mode' => $data['margin_mode'],
            'position_mode' => 'one_way',
            'entry_price' => $entryPrice,
            'quantity' => $data['quantity'],
            'margin' => $margin,
            'liquidation_price' => max($liquidationPrice, 0),
            'status' => 'open',
            'opened_at' => now(),
        ]);

        AuditLog::record($user, 'futures_position.opened', FuturesPosition::class, $position->id);

        return back()->with('success', "Opened {$data['side']} position on {$market->symbol} at {$data['leverage']}x with \${$margin} margin.");
    }

    public function close(FuturesPosition $position, LedgerService $ledger, PricingService $pricing)
    {
        abort_unless($position->user_id === Auth::id(), 403);
        abort_unless($position->status === 'open', 400);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $position->user_id, 'type' => WalletAccount::TYPE_TRADING]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_TRADING);

        $market = $position->market;
        $exitPrice = $pricing->usdPrice($market->asset) ?: (float) $market->mark_price;
        $market->update(['mark_price' => $exitPrice, 'index_price' => $exitPrice]);

        $direction = $position->side === 'long' ? 1 : -1;
        $pnl = round(($exitPrice - (float) $position->entry_price) * (float) $position->quantity * $direction, 2);
        $pnl = max($pnl, -1 * (float) $position->margin); // isolated margin: cannot lose more than the margin posted

        $ledger->unlockFunds($wallet, $usdt, (string) $position->margin);

        if ($pnl > 0) {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $pnl],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $pnl],
                ],
                referenceType: 'futures_pnl',
                referenceId: $position->id,
                description: 'Futures position closed with gain',
            );
        } elseif ($pnl < 0) {
            $loss = min(abs($pnl), $position->margin);
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $loss],
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $loss],
                ],
                referenceType: 'futures_pnl',
                referenceId: $position->id,
                description: 'Futures position closed with loss',
            );
            $pnl = -$loss;
        }

        $position->update(['status' => 'closed', 'pnl' => $pnl, 'closed_at' => now()]);

        AuditLog::record(Auth::user(), 'futures_position.closed', FuturesPosition::class, $position->id);

        return back()->with('success', 'Position closed. P&L: $'.number_format($pnl, 2));
    }
}
