<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RequiresTwoFactor;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Balance;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LockedBalanceController extends Controller
{
    use RequiresTwoFactor;

    public function index(Request $request)
    {
        $query = Balance::where('locked', '>', 0)->with('walletAccount.user', 'asset');

        if ($request->filled('wallet_type')) {
            $query->whereHas('walletAccount', fn ($q) => $q->where('type', $request->input('wallet_type')));
        }

        if ($request->filled('user')) {
            $search = $request->input('user');
            $query->whereHas('walletAccount.user', fn ($q) => $q->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->input('asset_id'));
        }

        $balances = $query->orderByDesc('locked')->paginate(25)->withQueryString();
        $assets = Asset::orderBy('symbol')->get();

        return view('admin.wallets.locked-balances', compact('balances', 'assets'));
    }

    public function unlock(Request $request, Balance $balance, LedgerService $ledger)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        if ($error = $this->verifyTwoFactor($request, Auth::user())) {
            return back()->with('error', $error);
        }

        $wallet = $balance->walletAccount;
        $asset = $balance->asset;
        $before = (float) $balance->locked;

        if ($before <= 0) {
            return back()->with('error', 'This balance has nothing locked.');
        }

        $ledger->unlockFunds($wallet, $asset, (string) $before);

        AuditLog::record(Auth::user(), 'locked_balance.unlocked', WalletAccount::class, $wallet->id, ['locked' => $before], [
            'locked' => 0,
            'user_id' => $wallet->user_id,
            'asset' => $asset->symbol,
            'reason' => $data['reason'],
        ]);

        return back()->with('success', "Unlocked {$before} {$asset->symbol} for ".($wallet->user->email ?? '#'.$wallet->user_id).' — moved from locked to available.');
    }

    public function updateLocked(Request $request, Balance $balance, LedgerService $ledger)
    {
        $data = $request->validate([
            'new_amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($error = $this->verifyTwoFactor($request, Auth::user())) {
            return back()->with('error', $error);
        }

        $wallet = $balance->walletAccount;
        $asset = $balance->asset;

        $result = $ledger->adjustLockedBalance($wallet, $asset, (string) $data['new_amount']);

        AuditLog::record(Auth::user(), 'locked_balance.edited', WalletAccount::class, $wallet->id, ['locked' => $result['before']], [
            'locked' => $result['after'],
            'user_id' => $wallet->user_id,
            'asset' => $asset->symbol,
            'reason' => $data['reason'],
        ]);

        return back()->with('success', "Locked balance for {$asset->symbol} changed from {$result['before']} to {$result['after']}.");
    }
}
