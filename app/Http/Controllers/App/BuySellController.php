<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\SwapTransaction;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\PricingService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuySellController extends Controller
{
    protected const FEE_PCT = 0.5;

    public function index()
    {
        $cryptoAssets = Asset::where('type', 'crypto')->where('is_active', true)->orderBy('symbol')->get();
        $fiat = Asset::where('symbol', 'USDT')->first();
        $history = SwapTransaction::where('user_id', Auth::id())->with('fromAsset', 'toAsset')->latest()->take(15)->get();

        return view('app.buy-sell.index', compact('cryptoAssets', 'fiat', 'history'));
    }

    public function store(Request $request, LedgerService $ledger, PricingService $pricing)
    {
        $user = Auth::user();

        $data = $request->validate([
            'side' => ['required', 'in:buy,sell'],
            'asset_id' => ['required', 'exists:assets,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $asset = Asset::findOrFail($data['asset_id']);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);

        $price = $pricing->usdPrice($asset);
        if ($price <= 0) {
            return back()->with('error', 'Pricing is unavailable for this asset right now.');
        }

        $fee = $data['amount'] * (self::FEE_PCT / 100);

        try {
            if ($data['side'] === 'buy') {
                // amount is in USDT to spend
                $cryptoAmount = ($data['amount'] - $fee) / $price;
                $ledger->post(
                    entries: [
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $data['amount']],
                        ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $data['amount']],
                        ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $cryptoAmount],
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $cryptoAmount],
                    ],
                    referenceType: 'buy_sell',
                    description: "Buy {$asset->symbol} with USDT",
                    createdBy: $user,
                );
                $fromAmount = $data['amount'];
                $toAmount = $cryptoAmount;
                $fromAsset = $usdt;
                $toAsset = $asset;
            } else {
                // amount is in crypto to sell
                $usdtAmount = ($data['amount'] * $price) * (1 - self::FEE_PCT / 100);
                $ledger->post(
                    entries: [
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $data['amount']],
                        ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $data['amount']],
                        ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $usdtAmount],
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $usdtAmount],
                    ],
                    referenceType: 'buy_sell',
                    description: "Sell {$asset->symbol} for USDT",
                    createdBy: $user,
                );
                $fromAmount = $data['amount'];
                $toAmount = $usdtAmount;
                $fromAsset = $asset;
                $toAsset = $usdt;
            }
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient balance for this transaction.');
        }

        SwapTransaction::create([
            'user_id' => $user->id,
            'wallet_account_id' => $wallet->id,
            'from_asset_id' => $fromAsset->id,
            'to_asset_id' => $toAsset->id,
            'from_amount' => $fromAmount,
            'to_amount' => $toAmount,
            'rate' => $price,
            'fee' => $fee,
            'status' => 'completed',
        ]);

        AuditLog::record($user, 'buy_sell.executed');

        return back()->with('success', ucfirst($data['side']).' completed instantly (simulated pricing).');
    }
}
