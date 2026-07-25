<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\P2PAd;
use App\Models\P2PMessage;
use App\Models\P2PMerchantProfile;
use App\Models\P2POrder;
use App\Models\P2PPaymentMethod;
use App\Services\P2PService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class P2PController extends Controller
{
    public function __construct(private P2PService $p2p) {}

    public function index(): View
    {
        return view('app.p2p.index');
    }

    public function buy(Request $request): View
    {
        $ads = P2PAd::query()
            ->where('side', 'sell')
            ->where('status', 'active')
            ->where('is_visible', true)
            ->with(['asset', 'user', 'merchantProfile'])
            ->when($request->asset, fn ($q) => $q->whereHas('asset', fn ($a) => $a->where('symbol', $request->asset)))
            ->when($request->fiat, fn ($q) => $q->where('fiat_currency', $request->fiat))
            ->latest()
            ->paginate(20);

        return view('app.p2p.buy', [
            'ads' => $ads,
            'assets' => Asset::query()->whereIn('symbol', ['USDT', 'BTC', 'ETH', 'USDC'])->get(),
            'methods' => P2PPaymentMethod::query()->where('is_active', true)->get(),
        ]);
    }

    public function sell(Request $request): View
    {
        $ads = P2PAd::query()
            ->where('side', 'buy')
            ->where('status', 'active')
            ->where('is_visible', true)
            ->with(['asset', 'user', 'merchantProfile'])
            ->latest()
            ->paginate(20);

        return view('app.p2p.sell', compact('ads'));
    }

    public function createOrder(Request $request, int $adId): RedirectResponse
    {
        $data = $request->validate([
            'fiat_amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['nullable', 'string'],
            'user_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $ad = P2PAd::query()->findOrFail($adId);

        try {
            $order = $this->p2p->createOrder(
                $request->user(),
                $ad,
                $data['fiat_amount'],
                $data['payment_method'] ?? null,
                $data['user_note'] ?? null,
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('app.p2p.orders')->with('success', "P2P order {$order->uuid} created. Escrow locked.");
    }

    public function orders(Request $request): View
    {
        $userId = $request->user()->id;
        $orders = P2POrder::query()
            ->where(fn ($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->latest()
            ->paginate(20);

        return view('app.p2p.orders', compact('orders'));
    }

    public function markPaid(Request $request, int $id): RedirectResponse
    {
        $order = P2POrder::query()->findOrFail($id);

        try {
            $this->p2p->markPaid($order, $request->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Marked as paid. Waiting for seller release.');
    }

    public function release(Request $request, int $id): RedirectResponse
    {
        $order = P2POrder::query()->findOrFail($id);

        try {
            $this->p2p->release($order, $request->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Crypto released from escrow to buyer.');
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $order = P2POrder::query()->findOrFail($id);

        try {
            $this->p2p->cancel($order, $request->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Order cancelled.');
    }

    public function appeal(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'evidence_url' => ['nullable', 'url'],
        ]);

        $order = P2POrder::query()->findOrFail($id);

        try {
            $this->p2p->openAppeal($order, $request->user(), $data['reason'], $data['evidence_url'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Appeal opened for admin review.');
    }

    public function message(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $order = P2POrder::query()->findOrFail($id);
        abort_unless(in_array($request->user()->id, [$order->buyer_id, $order->seller_id], true), 403);

        P2PMessage::query()->create([
            'p2p_order_id' => $order->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Message sent.');
    }

    public function ads(Request $request): View
    {
        return view('app.p2p.ads', [
            'ads' => P2PAd::query()->where('user_id', $request->user()->id)->latest()->get(),
            'assets' => Asset::query()->whereIn('symbol', ['USDT', 'BTC', 'ETH', 'USDC'])->get(),
            'methods' => P2PPaymentMethod::query()->where('is_active', true)->get(),
        ]);
    }

    public function storeAd(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'side' => ['required', 'in:buy,sell'],
            'fiat_currency' => ['required', 'string', 'size:3'],
            'price' => ['required', 'numeric', 'gt:0'],
            'available_amount' => ['required', 'numeric', 'gt:0'],
            'min_limit' => ['required', 'numeric', 'gt:0'],
            'max_limit' => ['required', 'numeric', 'gte:min_limit'],
            'terms' => ['nullable', 'string'],
        ]);

        $merchant = P2PMerchantProfile::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['is_verified' => $request->user()->kycApproved()]
        );

        P2PAd::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'merchant_profile_id' => $merchant->id,
            'price_type' => 'fixed',
            'is_visible' => true,
            'status' => 'active',
        ]);

        return back()->with('success', 'P2P ad created.');
    }

    public function merchant(Request $request): View
    {
        $profile = P2PMerchantProfile::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['is_verified' => false]
        );

        return view('app.p2p.merchant', compact('profile'));
    }

    public function appeals(Request $request): View
    {
        $userId = $request->user()->id;
        $orders = P2POrder::query()
            ->where('status', 'appealed')
            ->where(fn ($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->latest()
            ->get();

        return view('app.p2p.appeals', compact('orders'));
    }
}
