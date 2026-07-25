<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\P2PAd;
use App\Models\P2PAppeal;
use App\Models\P2POrder;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use Illuminate\Http\Request;

class P2PController extends Controller
{
    public function index()
    {
        $stats = [
            'active_ads' => P2PAd::where('status', 'active')->count(),
            'open_orders' => P2POrder::whereIn('status', ['escrow_locked', 'awaiting_payment', 'paid'])->count(),
            'open_appeals' => P2PAppeal::where('status', 'open')->count(),
        ];

        return view('admin.p2p.index', compact('stats'));
    }

    public function orders()
    {
        $orders = P2POrder::with('buyer', 'seller', 'asset')->latest()->paginate(30);

        return view('admin.p2p.orders', compact('orders'));
    }

    public function ads()
    {
        $ads = P2PAd::with('user', 'asset')->latest()->paginate(30);

        return view('admin.p2p.ads', compact('ads'));
    }

    public function appeals()
    {
        $appeals = P2PAppeal::with('order.buyer', 'order.seller', 'raisedBy')->latest()->paginate(30);

        return view('admin.p2p.appeals', compact('appeals'));
    }

    public function resolveAppeal(Request $request, P2PAppeal $appeal, LedgerService $ledger)
    {
        $data = $request->validate([
            'resolution' => ['required', 'string', 'max:2000'],
            'action' => ['required', 'in:release_to_buyer,refund_to_seller'],
        ]);

        $order = $appeal->order;

        if ($data['action'] === 'release_to_buyer' && in_array($order->status, ['appealed', 'paid', 'escrow_locked'])) {
            $sellerWallet = WalletAccount::firstOrCreate(['user_id' => $order->seller_id, 'type' => WalletAccount::TYPE_PRIMARY]);
            $buyerWallet = WalletAccount::firstOrCreate(['user_id' => $order->buyer_id, 'type' => WalletAccount::TYPE_PRIMARY]);

            $ledger->releaseLockedFunds(
                from: $sellerWallet,
                to: $buyerWallet,
                asset: $order->asset,
                amount: (string) $order->crypto_amount,
                referenceType: 'p2p_appeal_resolution',
                referenceId: $order->id,
                description: "Appeal #{$appeal->id} resolved in favor of buyer",
            );
            $order->update(['status' => 'completed', 'released_at' => now()]);
        } elseif ($data['action'] === 'refund_to_seller') {
            $sellerWallet = WalletAccount::firstOrCreate(['user_id' => $order->seller_id, 'type' => WalletAccount::TYPE_PRIMARY]);
            $ledger->unlockFunds($sellerWallet, $order->asset, (string) $order->crypto_amount);
            $order->update(['status' => 'refunded']);
        }

        $appeal->update([
            'status' => 'resolved',
            'resolution' => $data['resolution'],
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        AuditLog::record(auth()->user(), 'p2p_appeal.resolved', P2PAppeal::class, $appeal->id);

        return back()->with('success', 'Appeal resolved.');
    }
}
