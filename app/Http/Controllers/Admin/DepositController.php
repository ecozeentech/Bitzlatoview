<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\App\FundingController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Deposit;
use App\Models\WalletAccount;
use App\Models\Withdrawal;
use App\Services\LedgerService;
use App\Services\TransactionalMailService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepositController extends Controller
{
    public function index()
    {
        $deposits = Deposit::with('user', 'asset', 'paymentMethod')->latest()->paginate(25);

        return view('admin.deposits.index', compact('deposits'));
    }

    /**
     * The only place a deposit ever actually credits a wallet: an admin has reviewed the
     * uploaded proof of payment against the selected payment method and confirms the funds
     * were genuinely received before this posts to the ledger.
     */
    public function credit(Request $request, Deposit $deposit, LedgerService $ledger, TransactionalMailService $mailer)
    {
        abort_unless($deposit->status === 'pending', 400, 'This deposit has already been processed.');

        $data = $request->validate([
            'credited_amount' => ['nullable', 'numeric', 'gt:0'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $amount = $data['credited_amount'] ?? $deposit->amount;
        $house = House::wallet($deposit->walletAccount->type ?? WalletAccount::TYPE_PRIMARY);

        $ledger->post(
            entries: [
                ['wallet_account_id' => $house->id, 'asset_id' => $deposit->asset_id, 'direction' => 'debit', 'amount' => $amount],
                ['wallet_account_id' => $deposit->wallet_account_id, 'asset_id' => $deposit->asset_id, 'direction' => 'credit', 'amount' => $amount],
            ],
            referenceType: 'deposit',
            referenceId: $deposit->id,
            description: "Deposit #{$deposit->id} confirmed and credited by admin",
            approvedBy: auth()->user(),
        );

        $deposit->update([
            'status' => 'credited',
            'amount' => $amount,
            'credited_at' => now(),
            'admin_note' => $data['admin_note'] ?? 'Verified against uploaded proof of payment and credited.',
        ]);

        AuditLog::record(auth()->user(), 'deposit.credited', Deposit::class, $deposit->id, null, ['amount' => $amount]);
        $mailer->send($deposit->user, 'deposit_received', [
            'name' => $deposit->user->name,
            'amount' => number_format((float) $amount, 8),
            'asset' => $deposit->asset->symbol,
        ]);

        return back()->with('success', 'Deposit verified and credited to the user\'s wallet.');
    }

    public function reject(Request $request, Deposit $deposit)
    {
        abort_unless($deposit->status === 'pending', 400, 'This deposit has already been processed.');

        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);
        $deposit->update(['status' => 'rejected'] + $data);
        AuditLog::record(auth()->user(), 'deposit.rejected', Deposit::class, $deposit->id);

        return back()->with('success', 'Deposit rejected.');
    }

    /**
     * Stream the uploaded proof-of-payment file. Stored on the private disk so it is never
     * publicly reachable — only an authenticated admin can view it via this route.
     */
    public function proof(Deposit $deposit)
    {
        abort_unless($deposit->proof_file_path && Storage::disk('local')->exists($deposit->proof_file_path), 404);

        return Storage::disk('local')->response($deposit->proof_file_path);
    }

    public function withdrawals()
    {
        $withdrawals = Withdrawal::with('user', 'asset')->latest()->paginate(25);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    /**
     * Step 1: admin confirms the request looks legitimate (identity/KYC/amount checks out)
     * but has NOT sent the money yet. No ledger movement happens here — funds stay locked.
     */
    public function approveWithdrawal(Withdrawal $withdrawal)
    {
        abort_unless($withdrawal->status === 'pending_review', 400);

        $withdrawal->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        AuditLog::record(auth()->user(), 'withdrawal.approved', Withdrawal::class, $withdrawal->id);

        return back()->with('success', 'Withdrawal approved. Send the funds externally using the destination details, then mark it completed.');
    }

    /**
     * Step 2: admin has actually sent the funds externally (bank transfer, crypto tx, etc.)
     * and confirms it here — only now does the ledger debit the user's wallet.
     */
    public function completeWithdrawal(Withdrawal $withdrawal, LedgerService $ledger, FundingController $fundingController, TransactionalMailService $mailer)
    {
        abort_unless(in_array($withdrawal->status, ['pending_review', 'approved']), 400);

        $fundingController->completeWithdrawal($withdrawal, $ledger, auth()->user());
        AuditLog::record(auth()->user(), 'withdrawal.completed', Withdrawal::class, $withdrawal->id);
        $mailer->send($withdrawal->user, 'withdrawal_approved', [
            'name' => $withdrawal->user->name,
            'amount' => number_format((float) $withdrawal->amount, 8),
            'asset' => $withdrawal->asset->symbol,
        ]);

        return back()->with('success', 'Withdrawal marked completed and debited from the user\'s wallet.');
    }

    public function rejectWithdrawal(Request $request, Withdrawal $withdrawal, LedgerService $ledger, TransactionalMailService $mailer)
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        abort_unless(in_array($withdrawal->status, ['pending_review', 'approved']), 400);

        $ledger->unlockFunds($withdrawal->walletAccount, $withdrawal->asset, (string) $withdrawal->amount);
        $withdrawal->update(['status' => 'rejected'] + $data);

        AuditLog::record(auth()->user(), 'withdrawal.rejected', Withdrawal::class, $withdrawal->id);
        $mailer->send($withdrawal->user, 'withdrawal_rejected', [
            'name' => $withdrawal->user->name,
            'reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', 'Withdrawal rejected and funds unlocked.');
    }
}
