<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\P2PAd;
use App\Models\P2PAppeal;
use App\Models\P2PMerchantProfile;
use App\Models\P2PMessage;
use App\Models\P2POrder;
use App\Models\P2PPaymentMethod;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class P2PController extends Controller
{
    public function buy(Request $request)
    {
        return $this->listing($request, 'sell');
    }

    public function sell(Request $request)
    {
        return $this->listing($request, 'buy');
    }

    protected function listing(Request $request, string $adSide)
    {
        $ads = P2PAd::where('side', $adSide)->where('status', 'active')
            ->when($request->asset_id, fn ($q) => $q->where('asset_id', $request->asset_id))
            ->when($request->fiat, fn ($q) => $q->where('fiat_currency', $request->fiat))
            ->with('user.p2pMerchantProfile', 'asset')
            ->orderByDesc('price')
            ->get();

        return view('app.p2p.listing', [
            'ads' => $ads,
            'mode' => $adSide === 'sell' ? 'buy' : 'sell',
            'assets' => Asset::where('type', 'crypto')->get(),
            'fiats' => P2PAd::distinct()->pluck('fiat_currency'),
        ]);
    }

    public function orders()
    {
        $userId = Auth::id();
        $orders = P2POrder::where('buyer_id', $userId)->orWhere('seller_id', $userId)
            ->with('ad', 'buyer', 'seller', 'asset')->latest()->get();

        return view('app.p2p.orders', compact('orders'));
    }

    public function showOrder(P2POrder $order)
    {
        $this->authorizeParty($order);
        $order->load('ad', 'buyer', 'seller', 'asset', 'messages.user', 'appeal', 'feedback');

        return view('app.p2p.order-show', compact('order'));
    }

    public function createOrder(Request $request, LedgerService $ledger, TransactionalMailService $mailer)
    {
        $data = $request->validate([
            'ad_id' => ['required', 'exists:p2p_ads,id'],
            'crypto_amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['nullable', 'string', 'max:255'],
        ]);

        $ad = P2PAd::findOrFail($data['ad_id']);
        $user = Auth::user();

        if ($ad->user_id === $user->id) {
            return back()->with('error', 'You cannot trade against your own ad.');
        }

        if ($ad->min_limit > $data['crypto_amount'] * $ad->price || $ad->max_limit < $data['crypto_amount'] * $ad->price) {
            return back()->with('error', 'Amount is outside this ad\'s limits.');
        }

        // ad.side = 'sell' means the ad owner is selling crypto, so the ad owner is the seller.
        $seller = $ad->side === 'sell' ? $ad->user : $user;
        $buyer = $ad->side === 'sell' ? $user : $ad->user;

        $sellerWallet = WalletAccount::firstOrCreate(['user_id' => $seller->id, 'type' => WalletAccount::TYPE_PRIMARY]);

        try {
            $ledger->lockFunds($sellerWallet, $ad->asset, (string) $data['crypto_amount']);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'The seller does not have sufficient available balance for this trade.');
        }

        $order = P2POrder::create([
            'p2p_ad_id' => $ad->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'asset_id' => $ad->asset_id,
            'fiat_currency' => $ad->fiat_currency,
            'crypto_amount' => $data['crypto_amount'],
            'fiat_amount' => round($data['crypto_amount'] * $ad->price, 4),
            'price' => $ad->price,
            'payment_method' => $data['payment_method'] ?? null,
            'status' => 'escrow_locked',
            'expires_at' => now()->addMinutes(30),
        ]);

        $ad->decrement('available_amount', $data['crypto_amount']);

        AuditLog::record($user, 'p2p_order.created', P2POrder::class, $order->id);
        $mailer->send($seller, 'p2p_order_opened', ['name' => $seller->name, 'order_id' => (string) $order->id, 'amount' => number_format((float) $data['crypto_amount'], 8), 'asset' => $ad->asset->symbol]);

        return redirect()->route('app.p2p.orders.show', $order)->with('success', 'Order created. Crypto is now held in escrow.');
    }

    public function markPaid(P2POrder $order, TransactionalMailService $mailer)
    {
        $this->authorizeParty($order);
        abort_unless(Auth::id() === $order->buyer_id, 403, 'Only the buyer can mark this order as paid.');
        abort_unless(in_array($order->status, ['escrow_locked', 'awaiting_payment']), 400);

        $order->update(['status' => 'paid', 'paid_at' => now()]);
        AuditLog::record(Auth::user(), 'p2p_order.marked_paid', P2POrder::class, $order->id);
        $mailer->send($order->seller, 'p2p_order_paid', ['name' => $order->seller->name, 'order_id' => (string) $order->id]);

        return back()->with('success', 'Marked as paid. Waiting for the seller to confirm and release.');
    }

    public function release(P2POrder $order, LedgerService $ledger)
    {
        $this->authorizeParty($order);
        abort_unless(Auth::id() === $order->seller_id, 403, 'Only the seller can release escrow.');
        abort_unless(in_array($order->status, ['paid', 'escrow_locked']), 400);

        $sellerWallet = WalletAccount::firstOrCreate(['user_id' => $order->seller_id, 'type' => WalletAccount::TYPE_PRIMARY]);
        $buyerWallet = WalletAccount::firstOrCreate(['user_id' => $order->buyer_id, 'type' => WalletAccount::TYPE_PRIMARY]);

        $ledger->releaseLockedFunds(
            from: $sellerWallet,
            to: $buyerWallet,
            asset: $order->asset,
            amount: (string) $order->crypto_amount,
            referenceType: 'p2p_order',
            referenceId: $order->id,
            description: "P2P escrow release for order #{$order->id}",
        );

        $order->update(['status' => 'completed', 'released_at' => now()]);

        if ($merchant = P2PMerchantProfile::where('user_id', $order->seller_id)->first()) {
            $merchant->increment('completed_orders');
        }

        AuditLog::record(Auth::user(), 'p2p_order.released', P2POrder::class, $order->id);

        return back()->with('success', 'Escrow released to the buyer. Trade completed.');
    }

    public function cancelOrder(P2POrder $order, LedgerService $ledger)
    {
        $this->authorizeParty($order);
        abort_unless(in_array($order->status, ['escrow_locked', 'awaiting_payment']), 400, 'This order can no longer be cancelled.');

        $sellerWallet = WalletAccount::firstOrCreate(['user_id' => $order->seller_id, 'type' => WalletAccount::TYPE_PRIMARY]);
        $ledger->unlockFunds($sellerWallet, $order->asset, (string) $order->crypto_amount);

        $order->ad->increment('available_amount', $order->crypto_amount);
        $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        AuditLog::record(Auth::user(), 'p2p_order.cancelled', P2POrder::class, $order->id);

        return back()->with('success', 'Order cancelled and escrowed funds released back to the seller.');
    }

    public function sendMessage(Request $request, P2POrder $order)
    {
        $this->authorizeParty($order);

        $data = $request->validate(['message' => ['required', 'string', 'max:2000']]);

        P2PMessage::create([
            'p2p_order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => $data['message'],
        ]);

        return back()->with('success', 'Message sent.');
    }

    public function appeal(Request $request, P2POrder $order, TransactionalMailService $mailer)
    {
        $this->authorizeParty($order);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'evidence_url' => ['nullable', 'url'],
        ]);

        P2PAppeal::create($data + [
            'p2p_order_id' => $order->id,
            'raised_by' => Auth::id(),
            'status' => 'open',
        ]);

        $order->update(['status' => 'appealed']);
        AuditLog::record(Auth::user(), 'p2p_order.appealed', P2POrder::class, $order->id);

        $counterparty = Auth::id() === $order->buyer_id ? $order->seller : $order->buyer;
        $mailer->send($counterparty, 'p2p_appeal_opened', ['name' => $counterparty->name, 'order_id' => (string) $order->id]);

        return back()->with('success', 'Appeal opened. Our compliance team will review the chat, evidence and notes.');
    }

    public function myAds()
    {
        $ads = P2PAd::where('user_id', Auth::id())->with('asset')->latest()->get();
        $paymentMethods = P2PPaymentMethod::where('user_id', Auth::id())->get();

        return view('app.p2p.ads', [
            'ads' => $ads,
            'assets' => Asset::where('type', 'crypto')->get(),
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function storeAd(Request $request)
    {
        $data = $request->validate([
            'side' => ['required', 'in:buy,sell'],
            'asset_id' => ['required', 'exists:assets,id'],
            'fiat_currency' => ['required', 'string', 'max:8'],
            'price_type' => ['required', 'in:fixed,floating'],
            'price' => ['required', 'numeric', 'gt:0'],
            'min_limit' => ['required', 'numeric', 'gt:0'],
            'max_limit' => ['required', 'numeric', 'gt:0'],
            'available_amount' => ['required', 'numeric', 'gt:0'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'auto_reply' => ['nullable', 'string', 'max:500'],
            'region' => ['nullable', 'string', 'max:100'],
        ]);

        P2PAd::create($data + ['user_id' => Auth::id(), 'status' => 'active']);

        AuditLog::record(Auth::user(), 'p2p_ad.created');

        return back()->with('success', 'Ad published.');
    }

    public function updateAd(Request $request, P2PAd $ad)
    {
        abort_unless($ad->user_id === Auth::id(), 403);

        $data = $request->validate([
            'status' => ['required', 'in:active,paused,closed'],
        ]);

        $ad->update($data);

        return back()->with('success', 'Ad updated.');
    }

    public function merchant()
    {
        $profile = P2PMerchantProfile::where('user_id', Auth::id())->first();

        return view('app.p2p.merchant', compact('profile'));
    }

    public function applyMerchant(Request $request)
    {
        $data = $request->validate(['display_name' => ['required', 'string', 'max:100']]);

        P2PMerchantProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $data + ['status' => 'active', 'is_verified' => false]
        );

        AuditLog::record(Auth::user(), 'p2p_merchant.applied');

        return back()->with('success', 'Merchant application submitted. Verification badge is granted after admin review.');
    }

    public function storePaymentMethod(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
        ]);

        P2PPaymentMethod::create($data + ['user_id' => Auth::id()]);

        return back()->with('success', 'Payment method saved.');
    }

    public function appeals()
    {
        $appeals = P2PAppeal::where('raised_by', Auth::id())->with('order')->latest()->get();

        return view('app.p2p.appeals', compact('appeals'));
    }

    protected function authorizeParty(P2POrder $order): void
    {
        abort_unless(in_array(Auth::id(), [$order->buyer_id, $order->seller_id]), 403);
    }
}
