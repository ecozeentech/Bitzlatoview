<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SignalPackage;
use App\Models\SignalSubscription;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Http\Request;

class SignalController extends Controller
{
    public function index()
    {
        $packages = SignalPackage::withCount('subscriptions')->latest()->get();
        $subscriptions = SignalSubscription::with('user', 'package')->latest()->take(50)->get();

        return view('admin.signals.index', compact('packages', 'subscriptions'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $package = SignalPackage::create($data);

        AuditLog::record(auth()->user(), 'signal_package.created', SignalPackage::class, $package->id);

        return back()->with('success', "Signal package \"{$package->name}\" created.");
    }

    public function update(Request $request, SignalPackage $package)
    {
        $data = $this->validated($request);
        $package->update($data);

        AuditLog::record(auth()->user(), 'signal_package.updated', SignalPackage::class, $package->id);

        return back()->with('success', "Signal package \"{$package->name}\" updated.");
    }

    public function toggle(SignalPackage $package)
    {
        $package->update(['status' => $package->status === 'active' ? 'paused' : 'active']);

        AuditLog::record(auth()->user(), 'signal_package.status_toggled', SignalPackage::class, $package->id);

        return back()->with('success', "Signal package is now {$package->status}.");
    }

    public function destroy(SignalPackage $package)
    {
        if ($package->subscriptions()->exists()) {
            return back()->with('error', 'Cannot delete a package with existing subscriptions — pause it instead.');
        }

        AuditLog::record(auth()->user(), 'signal_package.deleted', SignalPackage::class, $package->id);
        $package->delete();

        return back()->with('success', 'Signal package deleted.');
    }

    /**
     * Manually correct a subscription's settled P&L (e.g. to fix a data error after stop).
     * Only allowed on already-stopped subscriptions, and always posts a real, audited ledger
     * correction for the delta — never just edits the number on screen.
     */
    public function adjustReturn(Request $request, SignalSubscription $subscription, LedgerService $ledger)
    {
        abort_unless($subscription->status === 'stopped', 422, 'Only stopped (settled) subscriptions can be adjusted.');

        $data = $request->validate([
            'new_pnl' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $delta = round((float) $data['new_pnl'] - (float) $subscription->pnl, 2);
        if (abs($delta) < 0.01) {
            return back()->with('error', 'No change — new P&L matches the current value.');
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $subscription->user_id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = \App\Models\Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_INVESTMENT);

        if ($delta > 0) {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $delta],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $delta],
                ],
                referenceType: 'signal_pnl_admin_correction',
                referenceId: $subscription->id,
                description: 'Admin correction: '.$data['reason'],
                createdBy: auth()->user(),
            );
        } else {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => abs($delta)],
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => abs($delta)],
                ],
                referenceType: 'signal_pnl_admin_correction',
                referenceId: $subscription->id,
                description: 'Admin correction: '.$data['reason'],
                createdBy: auth()->user(),
            );
        }

        $subscription->update(['pnl' => $data['new_pnl']]);

        AuditLog::record(auth()->user(), 'signal.pnl_adjusted', SignalSubscription::class, $subscription->id, ['pnl' => (float) $subscription->getOriginal('pnl')], ['pnl' => (float) $data['new_pnl'], 'reason' => $data['reason']]);

        return back()->with('success', 'P&L adjusted and ledger correction posted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'risk_level' => ['required', 'in:low,moderate,high'],
            'min_investment' => ['required', 'numeric', 'min:0'],
            'max_investment' => ['nullable', 'numeric', 'gte:min_investment'],
            'expected_return_pct' => ['required', 'numeric'],
            'duration_days' => ['required', 'integer', 'min:0'],
            'fee_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'tracked_asset_symbol' => ['required', 'string', 'max:15'],
            'status' => ['required', 'in:active,paused,retired'],
        ]);
    }
}
