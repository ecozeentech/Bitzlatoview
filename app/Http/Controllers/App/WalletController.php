<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Transfer;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function show(string $type, PricingService $pricing)
    {
        $user = Auth::user();
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $type]);
        $wallet->load('balances.asset');

        $assets = Asset::where('is_active', true)->orderBy('symbol')->get();

        $rows = $assets->map(function (Asset $asset) use ($wallet, $pricing) {
            $balance = $wallet->balances->firstWhere('asset_id', $asset->id);

            return [
                'asset' => $asset,
                'available' => (float) ($balance->available ?? 0),
                'locked' => (float) ($balance->locked ?? 0),
                'usd' => ((float) ($balance->available ?? 0) + (float) ($balance->locked ?? 0)) * $pricing->usdPrice($asset),
            ];
        })->filter(fn ($row) => $row['available'] > 0 || $row['locked'] > 0 || true)->sortByDesc('usd')->values();

        $history = Transfer::where('user_id', $user->id)
            ->where(fn ($q) => $q->where('from_wallet_account_id', $wallet->id)->orWhere('to_wallet_account_id', $wallet->id))
            ->with('asset', 'fromWallet', 'toWallet')
            ->latest()
            ->take(15)
            ->get();

        return view('app.wallet.show', [
            'wallet' => $wallet,
            'type' => $type,
            'rows' => $rows,
            'total' => $rows->sum('usd'),
            'history' => $history,
            'otherWallets' => collect(WalletAccount::TYPES)->reject(fn ($t) => $t === $type),
        ]);
    }

    public function transfer(Request $request, LedgerService $ledger)
    {
        $user = Auth::user();

        $data = $request->validate([
            'from_type' => ['required', 'in:primary,trading,investment'],
            'to_type' => ['required', 'in:primary,trading,investment', 'different:from_type'],
            'asset_id' => ['required', 'exists:assets,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $from = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $data['from_type']]);
        $to = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $data['to_type']]);
        $asset = Asset::findOrFail($data['asset_id']);

        try {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $from->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $data['amount']],
                    ['wallet_account_id' => $to->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $data['amount']],
                ],
                referenceType: 'transfer',
                description: "Internal transfer {$data['from_type']} -> {$data['to_type']}",
                createdBy: $user,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient available balance in the source wallet.');
        }

        $transfer = Transfer::create([
            'user_id' => $user->id,
            'from_wallet_account_id' => $from->id,
            'to_wallet_account_id' => $to->id,
            'asset_id' => $asset->id,
            'amount' => $data['amount'],
            'user_note' => $data['note'] ?? null,
            'status' => 'completed',
        ]);

        AuditLog::record($user, 'wallet.transfer', Transfer::class, $transfer->id);

        return redirect('/app/wallet/'.$data['to_type'])->with('success', 'Transfer completed.');
    }
}
