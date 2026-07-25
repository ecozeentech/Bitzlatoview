<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\InvestmentProduct;
use App\Models\InvestmentSubscription;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\RewardAccrualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    public function index(RewardAccrualService $accrual)
    {
        $accrual->accrueInvestments(Auth::user());

        $products = InvestmentProduct::where('status', 'active')->with('asset')->get();
        $subscriptions = InvestmentSubscription::where('user_id', Auth::id())->with('product.asset', 'rewards')->latest()->get();

        return view('app.investments.index', compact('products', 'subscriptions'));
    }

    public function subscribe(Request $request, InvestmentProduct $product, LedgerService $ledger)
    {
        $user = Auth::user();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$product->min_amount],
        ]);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_INVESTMENT]);

        try {
            $ledger->lockFunds($wallet, $product->asset, (string) $data['amount']);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient available balance in your Investment Wallet.');
        }

        $subscription = InvestmentSubscription::create([
            'user_id' => $user->id,
            'investment_product_id' => $product->id,
            'amount' => $data['amount'],
            'start_date' => now()->toDateString(),
            'unlock_date' => $product->lock_days > 0 ? now()->addDays($product->lock_days)->toDateString() : null,
            'status' => 'active',
        ]);

        AuditLog::record($user, 'investment.subscribed', InvestmentSubscription::class, $subscription->id);

        return back()->with('success', "Subscribed \${$data['amount']} to {$product->name}.");
    }

    public function redeem(InvestmentSubscription $subscription, LedgerService $ledger)
    {
        abort_unless($subscription->user_id === Auth::id(), 403);

        if ($subscription->unlock_date && $subscription->unlock_date->isFuture()) {
            return back()->with('error', 'This subscription is locked until '.$subscription->unlock_date->format('M d, Y').'.');
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $subscription->user_id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $ledger->unlockFunds($wallet, $subscription->product->asset, (string) $subscription->amount);

        $subscription->update(['status' => 'redeemed']);

        AuditLog::record(Auth::user(), 'investment.redeemed', InvestmentSubscription::class, $subscription->id);

        return back()->with('success', 'Redeemed. Principal is now available in your Investment Wallet.');
    }
}
