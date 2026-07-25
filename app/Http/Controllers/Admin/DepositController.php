<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\App\FundingController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Services\LedgerService;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function index()
    {
        $deposits = Deposit::with('user', 'asset')->latest()->paginate(25);

        return view('admin.deposits.index', compact('deposits'));
    }

    public function credit(Deposit $deposit)
    {
        $deposit->update(['status' => 'credited', 'credited_at' => now(), 'admin_note' => 'Manually confirmed by admin.']);
        AuditLog::record(auth()->user(), 'deposit.manually_credited', Deposit::class, $deposit->id);

        return back()->with('success', 'Deposit marked as credited.');
    }

    public function reject(Request $request, Deposit $deposit)
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);
        $deposit->update(['status' => 'rejected'] + $data);
        AuditLog::record(auth()->user(), 'deposit.rejected', Deposit::class, $deposit->id);

        return back()->with('success', 'Deposit rejected.');
    }

    public function withdrawals()
    {
        $withdrawals = Withdrawal::with('user', 'asset')->latest()->paginate(25);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function approveWithdrawal(Withdrawal $withdrawal, LedgerService $ledger, FundingController $fundingController)
    {
        abort_unless($withdrawal->status === 'pending_review', 400);

        $fundingController->completeWithdrawal($withdrawal, $ledger, auth()->user());
        AuditLog::record(auth()->user(), 'withdrawal.approved', Withdrawal::class, $withdrawal->id);

        return back()->with('success', 'Withdrawal approved and completed.');
    }

    public function rejectWithdrawal(Request $request, Withdrawal $withdrawal, LedgerService $ledger)
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        abort_unless(in_array($withdrawal->status, ['pending_review', 'approved']), 400);

        $ledger->unlockFunds($withdrawal->walletAccount, $withdrawal->asset, (string) $withdrawal->amount);
        $withdrawal->update(['status' => 'rejected'] + $data);

        AuditLog::record(auth()->user(), 'withdrawal.rejected', Withdrawal::class, $withdrawal->id);

        return back()->with('success', 'Withdrawal rejected and funds unlocked.');
    }

    public function completeWithdrawal(Withdrawal $withdrawal)
    {
        $withdrawal->update(['status' => 'completed', 'completed_at' => now()]);

        return back()->with('success', 'Withdrawal marked completed.');
    }
}
