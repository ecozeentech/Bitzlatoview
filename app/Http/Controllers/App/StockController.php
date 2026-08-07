<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\StockInstrument;
use App\Models\StockOrder;
use App\Models\StockPosition;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index()
    {
        $instruments = StockInstrument::orderBy('symbol')->get();
        $positions = StockPosition::where('user_id', Auth::id())->where('quantity', '>', 0)->with('instrument')->get();
        $orders = StockOrder::where('user_id', Auth::id())->with('instrument')->latest()->take(15)->get();

        return view('app.stocks.index', compact('instruments', 'positions', 'orders'));
    }

    public function store(Request $request, StockInstrument $instrument, LedgerService $ledger)
    {
        $user = Auth::user();

        $data = $request->validate([
            'side' => ['required', 'in:buy,sell'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_TRADING]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_TRADING);
        $cost = $instrument->last_price * $data['quantity'];

        $position = StockPosition::firstOrCreate(
            ['user_id' => $user->id, 'stock_instrument_id' => $instrument->id],
            ['quantity' => 0, 'avg_price' => 0]
        );

        if ($data['side'] === 'sell' && $position->quantity < $data['quantity']) {
            return back()->with('error', 'You do not hold enough shares to sell that quantity.');
        }

        try {
            if ($data['side'] === 'buy') {
                $ledger->post(
                    entries: [
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $cost],
                        ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $cost],
                    ],
                    referenceType: 'stock_order',
                    description: "Paper buy {$data['quantity']} {$instrument->symbol}",
                    createdBy: $user,
                );
                $newQty = $position->quantity + $data['quantity'];
                $position->avg_price = $newQty > 0 ? (($position->avg_price * $position->quantity) + $cost) / $newQty : 0;
                $position->quantity = $newQty;
            } else {
                $ledger->post(
                    entries: [
                        ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $cost],
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $cost],
                    ],
                    referenceType: 'stock_order',
                    description: "Paper sell {$data['quantity']} {$instrument->symbol}",
                    createdBy: $user,
                );
                $position->quantity -= $data['quantity'];
            }
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient balance for this trade.');
        }

        $position->save();

        StockOrder::create([
            'user_id' => $user->id,
            'stock_instrument_id' => $instrument->id,
            'side' => $data['side'],
            'quantity' => $data['quantity'],
            'price' => $instrument->last_price,
            'status' => 'filled',
        ]);

        AuditLog::record($user, 'stock_order.filled');

        return back()->with('success', 'Paper trade executed at $'.number_format($instrument->last_price, 2).'. Stock prices are not yet fed by a licensed market data vendor.');
    }
}
