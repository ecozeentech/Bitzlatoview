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

class SwapController extends Controller
{
    protected const FEE_PCT = 0.25;

    public function index()
    {
        $assets = Asset::where('is_active', true)->orderBy('symbol')->get();
        $history = SwapTransaction::where('user_id', Auth::id())->with('fromAsset', 'toAsset', 'walletAccount')->latest()->take(15)->get();

        return view('app.swap.index', compact('assets', 'history'));
    }

    public function quote(Request $request, PricingService $pricing)
    {
        $data = $request->validate([
            'from_asset_id' => ['required', 'exists:assets,id'],
            'to_asset_id' => ['required', 'exists:assets,id', 'different:from_asset_id'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $from = Asset::find($data['from_asset_id']);
        $to = Asset::find($data['to_asset_id']);

        $rate = $pricing->usdPrice($to) > 0 ? $pricing->usdPrice($from) / $pricing->usdPrice($to) : 0;
        $gross = $data['amount'] * $rate;
        $fee = $gross * (self::FEE_PCT / 100);
        $net = $gross - $fee;

        return response()->json([
            'rate' => $rate,
            'gross' => $gross,
            'fee' => $fee,
            'net' => $net,
            'min_received' => $net * 0.995,
        ]);
    }

    public function store(Request $request, LedgerService $ledger, PricingService $pricing)
    {
        $user = Auth::user();

        $data = $request->validate([
            'wallet_type' => ['required', 'in:primary,trading,investment'],
            'from_asset_id' => ['required', 'exists:assets,id'],
            'to_asset_id' => ['required', 'exists:assets,id', 'different:from_asset_id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'slippage_pct' => ['nullable', 'numeric', 'min:0.1', 'max:5'],
        ]);

        $from = Asset::findOrFail($data['from_asset_id']);
        $to = Asset::findOrFail($data['to_asset_id']);
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $data['wallet_type']]);
        $house = House::wallet($data['wallet_type']);

        $rate = $pricing->usdPrice($to) > 0 ? $pricing->usdPrice($from) / $pricing->usdPrice($to) : 0;
        if ($rate <= 0) {
            return back()->with('error', 'Swap pricing unavailable for this pair right now.');
        }

        $gross = $data['amount'] * $rate;
        $fee = $gross * (self::FEE_PCT / 100);
        $net = $gross - $fee;

        try {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $from->id, 'direction' => 'debit', 'amount' => $data['amount']],
                    ['wallet_account_id' => $house->id, 'asset_id' => $from->id, 'direction' => 'credit', 'amount' => $data['amount']],
                    ['wallet_account_id' => $house->id, 'asset_id' => $to->id, 'direction' => 'debit', 'amount' => $net],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $to->id, 'direction' => 'credit', 'amount' => $net],
                ],
                referenceType: 'swap',
                description: "Swap {$from->symbol} -> {$to->symbol}",
                createdBy: $user,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient available balance to swap.');
        }

        $swap = SwapTransaction::create([
            'user_id' => $user->id,
            'wallet_account_id' => $wallet->id,
            'from_asset_id' => $from->id,
            'to_asset_id' => $to->id,
            'from_amount' => $data['amount'],
            'to_amount' => $net,
            'rate' => $rate,
            'fee' => $fee,
            'slippage_pct' => $data['slippage_pct'] ?? 0.5,
            'status' => 'completed',
        ]);

        AuditLog::record($user, 'swap.executed', SwapTransaction::class, $swap->id);

        return back()->with('success', "Swapped {$data['amount']} {$from->symbol} for ".number_format($net, 8)." {$to->symbol}.");
    }
}
