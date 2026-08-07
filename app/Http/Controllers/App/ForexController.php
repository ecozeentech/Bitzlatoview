<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\ForexOrder;
use App\Models\ForexPair;
use App\Models\ForexPosition;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForexController extends Controller
{
    protected const MARGIN_PER_LOT = 1000;

    public function index()
    {
        $pairs = ForexPair::orderBy('symbol')->get();
        $positions = ForexPosition::where('user_id', Auth::id())->where('status', 'open')->with('pair')->get();
        $history = ForexOrder::where('user_id', Auth::id())->with('pair')->latest()->take(15)->get();

        return view('app.forex.index', compact('pairs', 'positions', 'history'));
    }

    public function store(Request $request, ForexPair $pair, LedgerService $ledger)
    {
        $user = Auth::user();

        $data = $request->validate([
            'side' => ['required', 'in:buy,sell'],
            'lot_size' => ['required', 'numeric', 'gt:0'],
            'leverage' => ['required', 'integer', 'in:10,20,50,100'],
        ]);

        $margin = round((self::MARGIN_PER_LOT * $data['lot_size']) / $data['leverage'], 2);
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_TRADING]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();

        try {
            $ledger->lockFunds($wallet, $usdt, (string) $margin);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient margin available in your Trading Wallet.');
        }

        $entryPrice = $data['side'] === 'buy' ? $pair->ask : $pair->bid;

        ForexOrder::create([
            'user_id' => $user->id,
            'forex_pair_id' => $pair->id,
            'side' => $data['side'],
            'lot_size' => $data['lot_size'],
            'leverage' => $data['leverage'],
            'entry_price' => $entryPrice,
            'status' => 'filled',
        ]);

        ForexPosition::create([
            'user_id' => $user->id,
            'forex_pair_id' => $pair->id,
            'side' => $data['side'],
            'lot_size' => $data['lot_size'],
            'leverage' => $data['leverage'],
            'entry_price' => $entryPrice,
            'current_price' => $entryPrice,
            'status' => 'open',
        ]);

        AuditLog::record($user, 'forex_position.opened');

        return back()->with('success', "Opened {$data['side']} {$data['lot_size']} lot on {$pair->symbol} with margin \${$margin} locked.");
    }

    public function close(ForexPosition $position, LedgerService $ledger)
    {
        abort_unless($position->user_id === Auth::id(), 403);
        abort_unless($position->status === 'open', 400);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $position->user_id, 'type' => WalletAccount::TYPE_TRADING]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_TRADING);
        $margin = round((self::MARGIN_PER_LOT * $position->lot_size) / $position->leverage, 2);
        $pair = $position->pair;

        // Mark-to-market against the pair's current bid/ask. Bitzlatoview does not yet have
        // a licensed live forex data feed connected (unlike crypto, which uses real CoinGecko
        // prices) — until one is, rates only move when an admin updates them, so P&L reflects
        // genuine price change rather than a random number.
        $exitPrice = (float) ($position->side === 'buy' ? $pair->bid : $pair->ask);
        $entryPrice = (float) $position->entry_price;
        $direction = $position->side === 'buy' ? 1 : -1;
        $notional = self::MARGIN_PER_LOT * $position->lot_size;
        $pnl = $entryPrice > 0 ? round((($exitPrice - $entryPrice) / $entryPrice) * $notional * $direction, 2) : 0.0;
        $pnl = max($pnl, -$margin);

        $ledger->unlockFunds($wallet, $usdt, (string) $margin);

        if ($pnl > 0) {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $pnl],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $pnl],
                ],
                referenceType: 'forex_pnl',
                referenceId: $position->id,
                description: 'Forex position closed with gain',
            );
        } elseif ($pnl < 0) {
            $loss = min(abs($pnl), $margin);
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $loss],
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $loss],
                ],
                referenceType: 'forex_pnl',
                referenceId: $position->id,
                description: 'Forex position closed with loss',
            );
            $pnl = -$loss;
        }

        $position->update(['status' => 'closed', 'pnl' => $pnl]);

        AuditLog::record(Auth::user(), 'forex_position.closed', ForexPosition::class, $position->id);

        return back()->with('success', 'Position closed. P&L: $'.number_format($pnl, 2));
    }
}
