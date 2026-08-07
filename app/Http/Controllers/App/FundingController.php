<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Deposit;
use App\Models\Network;
use App\Models\PaymentMethod;
use App\Models\WalletAccount;
use App\Models\Withdrawal;
use App\Models\WithdrawalAddress;
use App\Services\LedgerService;
use App\Services\TransactionalMailService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FundingController extends Controller
{
    public function deposit(Request $request)
    {
        return view('app.funding.deposit', [
            'assets' => Asset::where('is_active', true)->orderBy('symbol')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('sort_order')->get(),
            'selectedWallet' => $request->query('wallet', 'primary'),
        ]);
    }

    public function storeDeposit(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'wallet_type' => ['required', 'in:primary,trading,investment'],
            'asset_id' => ['required', 'exists:assets,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:500'],
            'proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $method = PaymentMethod::where('is_active', true)->findOrFail($data['payment_method_id']);

        if ($data['amount'] < $method->min_amount || ($method->max_amount && $data['amount'] > $method->max_amount)) {
            return back()->withInput()->with('error', "This payment method accepts amounts between {$method->min_amount} and ".($method->max_amount ?? '∞')." {$method->currency}.");
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $data['wallet_type']]);
        $asset = Asset::findOrFail($data['asset_id']);

        $proofPath = $request->file('proof_file')->store('deposit-proofs', 'local');
        $referenceCode = 'BZV-'.strtoupper(Str::random(8));

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'wallet_account_id' => $wallet->id,
            'asset_id' => $asset->id,
            'payment_method_id' => $method->id,
            'reference_code' => $referenceCode,
            'proof_file_path' => $proofPath,
            'amount' => $data['amount'],
            'status' => 'pending',
            'user_note' => $data['note'] ?? null,
        ]);

        AuditLog::record($user, 'deposit.requested', Deposit::class, $deposit->id);

        return redirect('/app/funding/transactions')->with('success', "Deposit request #{$deposit->id} submitted (reference {$referenceCode}). Our team verifies proof of payment manually and credits your wallet once confirmed — this is not instant.");
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

    public function storeWithdraw(Request $request, LedgerService $ledger, TransactionalMailService $mailer)
    {
        $user = Auth::user();

        $data = $request->validate([
            'wallet_type' => ['required', 'in:primary,trading,investment'],
            'asset_id' => ['required', 'exists:assets,id'],
            'network_id' => ['nullable', 'exists:networks,id'],
            'payment_method_type' => ['required', 'in:'.implode(',', PaymentMethod::TYPES)],
            'address' => ['required', 'string', 'max:255'],
            'destination_details' => ['nullable', 'string', 'max:1000'],
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
            'payment_method_type' => $data['payment_method_type'],
            'destination_details' => $data['destination_details'] ?? null,
            'amount' => $data['amount'],
            'fee' => $fee,
            'address' => $data['address'],
            'status' => 'pending_review',
            'user_note' => $data['note'] ?? null,
        ]);

        AuditLog::record($user, 'withdrawal.requested', Withdrawal::class, $withdrawal->id);
        $mailer->send($user, 'withdrawal_requested', [
            'name' => $user->name,
            'amount' => number_format((float) $data['amount'], 8),
            'asset' => $asset->symbol,
        ]);

        return redirect('/app/funding/transactions')->with('success', "Withdrawal request #{$withdrawal->id} submitted. Funds are locked in your wallet and will be sent by an administrator after manual verification — every withdrawal requires human review before any money moves.");
    }

    /**
     * Called by an admin (App\Http\Controllers\Admin\DepositController) after they have
     * actually sent the funds externally and confirmed the transfer went through.
     */
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

        $deposits = Deposit::where('user_id', $user->id)->with('asset', 'paymentMethod')->latest()->take(25)->get();
        $withdrawals = Withdrawal::where('user_id', $user->id)->with('asset')->latest()->take(25)->get();

        return view('app.funding.transactions', compact('deposits', 'withdrawals'));
    }
}
