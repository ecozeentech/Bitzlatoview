<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\BillingPackage;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function index()
    {
        $packages = BillingPackage::where('status', 'active')->with('analyst')->get();
        $subscriptions = Subscription::where('user_id', Auth::id())->with('package')->get();
        $invoices = Invoice::where('user_id', Auth::id())->latest()->get();

        return view('app.billing.index', compact('packages', 'subscriptions', 'invoices'));
    }

    public function subscribe(BillingPackage $package, LedgerService $ledger)
    {
        $user = Auth::user();

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);

        try {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $package->price],
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $package->price],
                ],
                referenceType: 'billing_subscription',
                referenceId: $package->id,
                description: "Subscribed to {$package->title}",
                createdBy: $user,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient balance in your Primary Wallet.');
        }

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'billing_package_id' => $package->id,
            'status' => 'active',
            'started_at' => now(),
            'renews_at' => now()->addMonth(),
        ]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'amount' => $package->price,
            'status' => 'paid',
            'line_items' => [['label' => $package->invoice_label, 'amount' => $package->price]],
            'issued_at' => now(),
        ]);

        AuditLog::record($user, 'billing.subscribed', Subscription::class, $subscription->id);

        return redirect()->route('app.billing.invoice', $invoice)->with('success', 'Subscribed successfully.');
    }

    public function cancel(Subscription $subscription)
    {
        abort_unless($subscription->user_id === Auth::id(), 403);
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return back()->with('success', 'Subscription cancelled.');
    }

    public function invoice(Invoice $invoice)
    {
        abort_unless($invoice->user_id === Auth::id(), 403);

        return view('app.billing.invoice', compact('invoice'));
    }
}
