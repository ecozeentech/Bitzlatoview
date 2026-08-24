<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\CopiedTrade;
use App\Models\CopyAllocation;
use App\Models\TraderProfile;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Http\Request;

class CopyTradingController extends Controller
{
    public function index()
    {
        $traders = TraderProfile::withCount('allocations')->latest()->get();
        $allocations = CopyAllocation::with('user', 'trader', 'trades')->latest()->take(50)->get();

        return view('admin.copy-trading.index', compact('traders', 'allocations'));
    }

    public function updateTrade(Request $request, CopiedTrade $trade)
    {
        abort_unless($trade->closed_at === null, 422, 'Only open (not yet settled) trades can be edited.');

        $data = $request->validate([
            'asset_symbol' => ['required', 'string', 'max:20'],
            'side' => ['required', 'in:long,short'],
            'entry_price' => ['required', 'numeric', 'gt:0'],
        ]);

        $before = $trade->only(array_keys($data));
        $trade->update($data);

        AuditLog::record(auth()->user(), 'copied_trade.updated', CopiedTrade::class, $trade->id, $before, $data);

        return back()->with('success', 'Copied trade updated.');
    }

    /**
     * Manually correct a stopped allocation's settled P&L. Always posts a real, audited ledger
     * correction for the delta — never a silent field edit.
     */
    public function adjustAllocationPnl(Request $request, CopyAllocation $allocation, LedgerService $ledger)
    {
        abort_unless($allocation->status === 'stopped', 422, 'Only stopped (settled) allocations can be adjusted.');

        $data = $request->validate([
            'new_pnl' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $delta = round((float) $data['new_pnl'] - (float) $allocation->pnl, 2);
        if (abs($delta) < 0.01) {
            return back()->with('error', 'No change — new P&L matches the current value.');
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $allocation->user_id, 'type' => WalletAccount::TYPE_PRIMARY]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);

        $entries = $delta > 0
            ? [
                ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $delta],
                ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $delta],
            ]
            : [
                ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => abs($delta)],
                ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => abs($delta)],
            ];

        $ledger->post(
            entries: $entries,
            referenceType: 'copy_trading_pnl_admin_correction',
            referenceId: $allocation->id,
            description: 'Admin correction: '.$data['reason'],
            createdBy: auth()->user(),
        );

        $oldPnl = (float) $allocation->pnl;
        $allocation->update(['pnl' => $data['new_pnl']]);

        AuditLog::record(auth()->user(), 'copy_trading.pnl_adjusted', CopyAllocation::class, $allocation->id, ['pnl' => $oldPnl], ['pnl' => (float) $data['new_pnl'], 'reason' => $data['reason']]);

        return back()->with('success', 'P&L adjusted and ledger correction posted.');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        TraderProfile::create($data + ['bio' => $data['bio'] ?: 'Performance figures are disclosed estimates pending independent verification of a real trading track record.']);

        return back()->with('success', 'Trader profile created.');
    }

    public function update(Request $request, TraderProfile $trader)
    {
        $data = $this->validated($request);

        $before = $trader->only(array_keys($data));
        $trader->update($data);

        AuditLog::record(auth()->user(), 'trader.updated', TraderProfile::class, $trader->id, $before, $data);

        return back()->with('success', 'Trader updated.');
    }

    public function toggleAvailability(TraderProfile $trader)
    {
        $newStatus = $trader->status === 'sold_out' ? 'active' : 'sold_out';
        $trader->update(['status' => $newStatus]);

        AuditLog::record(auth()->user(), 'trader.availability_toggled', TraderProfile::class, $trader->id, null, ['status' => $newStatus]);

        return back()->with('success', $newStatus === 'sold_out'
            ? "{$trader->display_name} is now marked Sold Out — no new users can start copying until you mark it available again."
            : "{$trader->display_name} is available for new copiers again.");
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'in:crypto,forex,futures,stock,p2p'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'strategy' => ['nullable', 'string', 'max:2000'],
            'risk_score' => ['required', 'integer', 'min:1', 'max:100'],
            'return_30d_pct' => ['required', 'numeric'],
            'return_90d_pct' => ['required', 'numeric'],
            'max_drawdown_pct' => ['required', 'numeric'],
            'win_rate_pct' => ['required', 'numeric'],
            'followers_count' => ['required', 'integer', 'min:0'],
            'min_copy_amount' => ['required', 'numeric', 'min:0'],
            'lock_days' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,suspended,pending_approval,sold_out'],
        ]);

        $data['is_verified'] = $request->boolean('is_verified');
        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }
}
