<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\KycSubmission;
use App\Models\P2PAppeal;
use App\Models\User;
use App\Models\VirtualCard;
use App\Models\Withdrawal;
use Illuminate\Support\Collection;

/**
 * Assembles a lightweight "recent activity" feed for the topbar notification bell out of
 * events that already exist (deposit/withdrawal decisions, KYC decisions, card requests, P2P
 * appeal resolutions) rather than a separate persisted notifications table — this is a read
 * model only, nothing here writes any new data.
 */
class NotificationFeedService
{
    public function recentFor(User $user, int $limit = 8): Collection
    {
        $items = collect();

        Deposit::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('updated_at')->take($limit)
            ->get()->each(function ($d) use ($items) {
                $items->push([
                    'icon' => $d->status === 'approved' ? 'success' : 'danger',
                    'text' => $d->status === 'approved'
                        ? "Deposit of {$d->amount} {$d->asset->symbol} was approved."
                        : "Deposit of {$d->amount} {$d->asset->symbol} was rejected.",
                    'url' => '/app/funding/transactions',
                    'at' => $d->updated_at,
                ]);
            });

        Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected', 'completed'])
            ->latest('updated_at')->take($limit)
            ->get()->each(function ($w) use ($items) {
                $items->push([
                    'icon' => $w->status === 'rejected' ? 'danger' : 'success',
                    'text' => match ($w->status) {
                        'completed' => "Withdrawal of {$w->amount} {$w->asset->symbol} was completed.",
                        'approved' => "Withdrawal of {$w->amount} {$w->asset->symbol} was approved.",
                        default => "Withdrawal of {$w->amount} {$w->asset->symbol} was rejected.",
                    },
                    'url' => '/app/funding/transactions',
                    'at' => $w->updated_at,
                ]);
            });

        KycSubmission::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('updated_at')->take($limit)
            ->get()->each(function ($k) use ($items) {
                $items->push([
                    'icon' => $k->status === 'approved' ? 'success' : 'danger',
                    'text' => $k->status === 'approved'
                        ? 'Your identity verification was approved.'
                        : 'Your identity verification needs attention.',
                    'url' => '/app/settings/kyc',
                    'at' => $k->updated_at,
                ]);
            });

        VirtualCard::where('user_id', $user->id)
            ->whereIn('status', ['active', 'rejected'])
            ->latest('updated_at')->take($limit)
            ->get()->each(function ($c) use ($items) {
                $items->push([
                    'icon' => $c->status === 'active' ? 'success' : 'danger',
                    'text' => $c->status === 'active'
                        ? "Your virtual card ending {$c->last_four} was approved."
                        : "Your virtual card request was declined.",
                    'url' => '/app/virtual-cards',
                    'at' => $c->updated_at,
                ]);
            });

        P2PAppeal::whereHas('order', fn ($q) => $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id))
            ->where('status', 'resolved')
            ->latest('updated_at')->take($limit)
            ->get()->each(function ($a) use ($items) {
                $items->push([
                    'icon' => 'info',
                    'text' => "Your P2P appeal on order #{$a->p2p_order_id} was resolved.",
                    'url' => "/app/p2p/orders/{$a->p2p_order_id}",
                    'at' => $a->updated_at,
                ]);
            });

        return $items->sortByDesc('at')->take($limit)->values();
    }
}
