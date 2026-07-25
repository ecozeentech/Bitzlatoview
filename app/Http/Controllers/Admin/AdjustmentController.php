<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\BalanceAdjustment;
use App\Models\User;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Http\Request;

class AdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = BalanceAdjustment::with('user', 'asset', 'walletAccount', 'requestedBy', 'approvedBy')->latest()->paginate(25);
        $users = User::where('role', 'user')->orderBy('name')->get();
        $assets = Asset::where('is_active', true)->get();

        return view('admin.adjustments.index', compact('adjustments', 'users', 'assets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'wallet_type' => ['required', 'in:primary,trading,investment'],
            'asset_id' => ['required', 'exists:assets,id'],
            'direction' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:1000'],
            'evidence_url' => ['nullable', 'url'],
        ]);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $data['user_id'], 'type' => $data['wallet_type']]);

        $adjustment = BalanceAdjustment::create([
            'user_id' => $data['user_id'],
            'wallet_account_id' => $wallet->id,
            'asset_id' => $data['asset_id'],
            'direction' => $data['direction'],
            'amount' => $data['amount'],
            'reason' => $data['reason'],
            'evidence_url' => $data['evidence_url'] ?? null,
            'requested_by' => auth()->id(),
            'status' => 'pending_approval',
        ]);

        AuditLog::record(auth()->user(), 'balance_adjustment.requested', BalanceAdjustment::class, $adjustment->id);

        return back()->with('success', 'Adjustment requested. Requires a second admin approval before funds move (maker/checker control).');
    }

    public function approve(BalanceAdjustment $adjustment, LedgerService $ledger)
    {
        abort_unless($adjustment->status === 'pending_approval', 400);

        $house = House::wallet($adjustment->walletAccount->type);
        $asset = $adjustment->asset;
        $amount = (string) $adjustment->amount;

        $entries = $adjustment->direction === 'credit'
            ? [
                ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                ['wallet_account_id' => $adjustment->wallet_account_id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
            ]
            : [
                ['wallet_account_id' => $adjustment->wallet_account_id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
            ];

        $transaction = $ledger->post(
            entries: $entries,
            referenceType: 'admin_adjustment',
            referenceId: $adjustment->id,
            description: "Admin balance adjustment: {$adjustment->reason}",
            createdBy: $adjustment->requestedBy,
            approvedBy: auth()->user(),
        );

        $adjustment->update([
            'status' => 'applied',
            'approved_by' => auth()->id(),
            'ledger_transaction_id' => $transaction->id,
        ]);

        AuditLog::record(auth()->user(), 'balance_adjustment.approved', BalanceAdjustment::class, $adjustment->id);

        return back()->with('success', 'Adjustment approved and applied through the ledger.');
    }

    public function reject(BalanceAdjustment $adjustment)
    {
        abort_unless($adjustment->status === 'pending_approval', 400);

        $adjustment->update(['status' => 'rejected', 'approved_by' => auth()->id()]);
        AuditLog::record(auth()->user(), 'balance_adjustment.rejected', BalanceAdjustment::class, $adjustment->id);

        return back()->with('success', 'Adjustment rejected.');
    }
}
