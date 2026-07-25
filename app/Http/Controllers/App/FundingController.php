<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Deposit;
use App\Models\Network;
use App\Models\WalletAccount;
use App\Models\Withdrawal;
use App\Models\WithdrawalAddress;
use App\Services\LedgerService;
use App\Services\PricingService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FundingController extends Controller
{
    /** Withdrawals above this USD-equivalent notional require manual admin review. */
    protected const AUTO_APPROVE_THRESHOLD = 500;

    public function deposit(Request $request)
    {
        $user = Auth::user();

        return view('app.funding.deposit', [
            'assets' => Asset::where('is_active', true)->orderBy('symbol')->get(),
            'networks' => Network::where('is_active', true)->get(),
            'selectedWallet' => $request->query('wallet', 'primary'),
            'depositAddress' => 'bzv-sim-'.Str::lower(Str::random(30)),
        ]);
    }

    public function storeDeposit(Request $request, LedgerService $ledger)
    {
        $user = Auth::user();

        $data = $request->validate([
            'wallet_type' => ['required', 'in:primary,trading,investment'],
            'asset_id' => ['required', 'exists:assets,id'],
            'network_id' => ['nullable', 'exists:networks,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $data['wallet_type']]);
        $asset = Asset::findOrFail($data['asset_id']);
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'wallet_account_id' => $wallet->id,
            'asset_id' => $asset->id,
            'network_id' => $data['network_id'] ?? null,
            'amount' => $data['amount'],
            'address' => 'bzv-sim-'.Str::lower(Str::random(30)),
            'tx_hash' => Str::lower(Str::random(64)),
            'status' => 'pending',
            'user_note' => $data['note'] ?? null,
        ]);

        // Simulation-mode: instantly "confirm" the deposit and post the ledger credit.
        $ledger->post(
            entries: [
                ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $data['amount']],
                ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $data['amount']],
            ],
            referenceType: 'deposit',
            referenceId: $deposit->id,
            description: "Simulated deposit of {$data['amount']} {$asset->symbol}",
            createdBy: $user,
        );

        $deposit->update(['status' => 'credited', 'credited_at' => now()]);

        AuditLog::record($user, 'deposit.credited', Deposit::class, $deposit->id);

        return redirect('/app/wallet/'.$data['wallet_type'])->with('success', "Deposit of {$data['amount']} {$asset->symbol} credited (simulated).");
    }

    public function withdraw(Request $request)
    {
        $user = Auth::user();

        return view('app.funding.withdraw', [
            'assets' => Asset::where('is_active', true)->orderBy('symbol')->get(),
            'networks' => Network::where('is_active', true)->get(),
            'addresses' => WithdrawalAddress::where('user_id', $user->id)->get(),
            'selectedWallet' => $request->query('wallet', 'primary'),
        ]);
    }

    public function storeWithdraw(Request $request, LedgerService $ledger, PricingService $pricing)
    {
        $user = Auth::user();

        $data = $request->validate([
            'wallet_type' => ['required', 'in:primary,trading,investment'],
            'asset_id' => ['required', 'exists:assets,id'],
            'network_id' => ['nullable', 'exists:networks,id'],
            'address' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $data['wallet_type']]);
        $asset = Asset::findOrFail($data['asset_id']);
        $fee = round($data['amount'] * 0.001, 8);

        try {
            $ledger->lockFunds($wallet, $asset, (string) $data['amount']);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient available balance for this withdrawal.');
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'wallet_account_id' => $wallet->id,
            'asset_id' => $asset->id,
            'network_id' => $data['network_id'] ?? null,
            'amount' => $data['amount'],
            'fee' => $fee,
            'address' => $data['address'],
            'status' => 'pending_review',
            'user_note' => $data['note'] ?? null,
        ]);

        AuditLog::record($user, 'withdrawal.requested', Withdrawal::class, $withdrawal->id);

        $usdEquivalent = $data['amount'] * $pricing->usdPrice($asset);
        $autoApprove = $usdEquivalent <= self::AUTO_APPROVE_THRESHOLD;

        if ($autoApprove) {
            $this->completeWithdrawal($withdrawal, $ledger, $user);

            return redirect('/app/funding/transactions')->with('success', 'Withdrawal auto-approved and completed (simulation mode, below review threshold).');
        }

        return redirect('/app/funding/transactions')->with('success', 'Withdrawal submitted and is pending compliance review because it exceeds the auto-approval threshold.');
    }

    public function completeWithdrawal(Withdrawal $withdrawal, LedgerService $ledger, $approver = null)
    {
        $wallet = $withdrawal->walletAccount;
        $asset = $withdrawal->asset;
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);

        $ledger->unlockFunds($wallet, $asset, (string) $withdrawal->amount);

        $ledger->post(
            entries: [
                ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $withdrawal->amount],
                ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $withdrawal->amount],
            ],
            referenceType: 'withdrawal',
            referenceId: $withdrawal->id,
            description: "Withdrawal of {$withdrawal->amount} {$asset->symbol}",
            approvedBy: $approver,
        );

        $withdrawal->update([
            'status' => 'completed',
            'approved_by' => $approver?->id,
            'approved_at' => now(),
            'completed_at' => now(),
        ]);
    }

    public function storeAddress(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'network_id' => ['nullable', 'exists:networks,id'],
            'address' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        WithdrawalAddress::create($data + [
            'user_id' => $user->id,
            'cooldown_until' => now()->addHours(24),
        ]);

        AuditLog::record($user, 'withdrawal_address.added');

        return back()->with('success', 'Address saved. New addresses are subject to a 24-hour withdrawal cooldown.');
    }

    public function transactions()
    {
        $user = Auth::user();

        $deposits = Deposit::where('user_id', $user->id)->with('asset')->latest()->take(25)->get();
        $withdrawals = Withdrawal::where('user_id', $user->id)->with('asset')->latest()->take(25)->get();

        return view('app.funding.transactions', compact('deposits', 'withdrawals'));
    }
}
