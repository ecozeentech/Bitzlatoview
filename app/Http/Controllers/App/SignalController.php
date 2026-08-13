<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\SignalPackage;
use App\Models\SignalSubscription;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignalController extends Controller
{
    public function index()
    {
        $packages = SignalPackage::where('status', 'active')->get();
        $mySubscriptions = SignalSubscription::where('user_id', Auth::id())->with('package')->latest()->get();

        return view('app.signals.index', compact('packages', 'mySubscriptions'));
    }

    public function show(SignalPackage $package)
    {
        $myAllocation = SignalSubscription::where('user_id', Auth::id())->where('signal_package_id', $package->id)->where('status', '!=', 'stopped')->first();

        return view('app.signals.subscribe', compact('package', 'myAllocation'));
    }

    public function subscribe(Request $request, SignalPackage $package, LedgerService $ledger, PricingService $pricing)
    {
        $user = Auth::user();

        abort_unless($package->status === 'active', 422, 'This signal package is not currently accepting new subscriptions.');

        $data = $request->validate([
            'amount' => array_filter([
                'required', 'numeric', 'min:'.$package->min_investment,
                $package->max_investment ? 'max:'.$package->max_investment : null,
            ]),
        ]);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();

        try {
            $ledger->lockFunds($wallet, $usdt, (string) $data['amount']);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient available balance in your Investment Wallet.');
        }

        $trackedAsset = Asset::where('symbol', $package->tracked_asset_symbol)->first();

        $subscription = SignalSubscription::create([
            'user_id' => $user->id,
            'signal_package_id' => $package->id,
            'amount' => $data['amount'],
            'entry_price' => $trackedAsset ? $pricing->usdPrice($trackedAsset) : null,
            'status' => 'active',
            'started_at' => now(),
            'unlocks_at' => $package->duration_days > 0 ? now()->addDays($package->duration_days) : null,
        ]);

        AuditLog::record($user, 'signal.subscribed', SignalSubscription::class, $subscription->id);

        return back()->with('success', "Subscribed \${$data['amount']} to {$package->name}. Signals settle against real {$package->tracked_asset_symbol} market price movement on Bitzlatoview's internal engine — expected return is a disclosed estimate, not a guarantee, and you may lose money.");
    }

    public function pause(SignalSubscription $subscription)
    {
        $this->authorizeOwner($subscription);
        $subscription->update(['status' => 'paused']);

        return back()->with('success', 'Signal subscription paused.');
    }

    public function resume(SignalSubscription $subscription)
    {
        $this->authorizeOwner($subscription);
        $subscription->update(['status' => 'active']);

        return back()->with('success', 'Signal subscription resumed.');
    }

    public function stop(SignalSubscription $subscription, LedgerService $ledger, PricingService $pricing)
    {
        $this->authorizeOwner($subscription);

        if ($subscription->unlocks_at && $subscription->unlocks_at->isFuture()) {
            return back()->with('error', 'This subscription is locked until '.$subscription->unlocks_at->format('M d, Y').'.');
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $subscription->user_id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = \App\Support\House::wallet(WalletAccount::TYPE_INVESTMENT);
        $package = $subscription->package;

        // Settle against real market price movement over the holding period rather than a
        // random number, same approach as AI Bots/Copy Trading — loss is capped at the
        // amount actually allocated (no leverage/debt here).
        $pnl = 0.0;
        $exitPrice = null;

        if ($subscription->entry_price > 0) {
            $trackedAsset = Asset::where('symbol', $package->tracked_asset_symbol)->first();
            $exitPrice = $trackedAsset ? $pricing->usdPrice($trackedAsset) : null;

            if ($exitPrice) {
                $priceChangePct = ($exitPrice - $subscription->entry_price) / $subscription->entry_price;
                $pnl = round((float) $subscription->amount * $priceChangePct, 2);
                $pnl -= round((float) $subscription->amount * ((float) $package->fee_pct / 100), 2);
                $pnl = max($pnl, -1 * (float) $subscription->amount);
            }
        }

        $ledger->unlockFunds($wallet, $usdt, (string) $subscription->amount);

        if ($pnl > 0) {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $pnl],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $pnl],
                ],
                referenceType: 'signal_pnl',
                referenceId: $subscription->id,
                description: 'Signal subscription gain settled on stop (based on real market price movement)',
            );
        } elseif ($pnl < 0) {
            $loss = min(abs($pnl), $subscription->amount);
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $loss],
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $loss],
                ],
                referenceType: 'signal_pnl',
                referenceId: $subscription->id,
                description: 'Signal subscription loss settled on stop (based on real market price movement)',
            );
            $pnl = -$loss;
        }

        $subscription->update(['status' => 'stopped', 'pnl' => $pnl, 'exit_price' => $exitPrice, 'stopped_at' => now()]);

        AuditLog::record(Auth::user(), 'signal.stopped', SignalSubscription::class, $subscription->id);

        return back()->with('success', 'Signal subscription stopped and funds released back to your Investment Wallet.');
    }

    protected function authorizeOwner(SignalSubscription $subscription): void
    {
        abort_unless($subscription->user_id === Auth::id(), 403);
    }
}
