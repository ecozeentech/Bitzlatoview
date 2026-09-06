<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Deposit;
use App\Models\Network;
use App\Models\PaymentMethod;
use App\Models\SystemSetting;
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
            'pendingWithdrawals' => Withdrawal::where('user_id', $user->id)
                ->whereIn('status', ['pending_review', 'approved', 'processing'])
                ->with('asset', 'walletAccount')->latest()->get(),
            'withdrawalFeeEnabled' => (bool) SystemSetting::getValue('withdrawal_fee_enabled', true),
            'withdrawalFeePct' => (float) SystemSetting::getValue('withdrawal_fee_percentage', 0.1),
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
        $fee = Withdrawal::calculateFee((float) $data['amount']);
        $netAmount = round((float) $data['amount'] - $fee, 8);

        if ($wallet->is_suspended) {
            return back()->with('error', $wallet->label().' is suspended and cannot be withdrawn from. Contact support for details.');
        }

        // The full requested amount is locked immediately — the fee is only carved out (and
        // credited to the platform's fee wallet) once the withdrawal is actually completed,
        // so a rejected withdrawal always unlocks the full amount with nothing deducted.
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
            'net_amount' => $netAmount,
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

        return redirect('/app/funding/transactions')->with('success', "Withdrawal request #{$withdrawal->id} submitted for ".number_format((float) $data['amount'], 8)." {$asset->symbol}. Funds are locked in your wallet and will be sent by an administrator after manual verification. Fee: ".number_format($fee, 8)." {$asset->symbol} — you will receive ".number_format($netAmount, 8)." {$asset->symbol}.");
    }

    /**
     * Called by an admin (App\Http\Controllers\Admin\DepositController) after they have
     * actually sent the funds externally and confirmed the transfer went through.
     */
    public function completeWithdrawal(Withdrawal $withdrawal, LedgerService $ledger, $approver = null)
    {
        $wallet = $withdrawal->walletAccount;
        $asset = $withdrawal->asset;
        // "Sent externally" leg mirrors the wallet type actually withdrawn from, so House's
        // per-wallet-type books stay balanced. The fee leg always lands in House's Primary
        // Wallet regardless of source — it's the platform's central fee-revenue bucket.
        $houseSource = House::wallet($wallet->type);
        $housePrimary = House::wallet(WalletAccount::TYPE_PRIMARY);

        $fee = (float) $withdrawal->fee;
        // Fall back to amount-minus-fee for any withdrawal that was created before net_amount
        // existed (or otherwise has it unset), rather than trusting a stale/zero column.
        $netAmount = (float) $withdrawal->net_amount > 0 ? (float) $withdrawal->net_amount : round((float) $withdrawal->amount - $fee, 8);

        $ledger->unlockFunds($wallet, $asset, (string) $withdrawal->amount);

        $entries = [
            ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $withdrawal->amount],
            ['wallet_account_id' => $houseSource->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $netAmount],
        ];

        if ($fee > 0) {
            $entries[] = ['wallet_account_id' => $housePrimary->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $fee];
        }

        $ledger->post(
            entries: $entries,
            referenceType: 'withdrawal',
            referenceId: $withdrawal->id,
            description: "Withdrawal of {$withdrawal->amount} {$asset->symbol} (fee {$fee} {$asset->symbol}, net {$netAmount} {$asset->symbol})",
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
