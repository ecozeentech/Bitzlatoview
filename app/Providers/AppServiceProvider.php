<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\User;
use App\Observers\UserObserver;
use App\Services\NotificationFeedService;
use App\Services\PricingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        View::composer('partials.app-topbar', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $notifications = app(NotificationFeedService::class)->recentFor(Auth::user());
            $lastSeenAt = session('notifications_last_seen_at');

            $view->with('notifications', $notifications);
            $view->with('unreadNotifications', $lastSeenAt
                ? $notifications->filter(fn ($n) => $n['at']->gt($lastSeenAt))->count()
                : $notifications->count());

            // Topbar balance summary (fiat total + BTC equivalent) shown next to Deposit/Withdraw.
            $pricing = app(PricingService::class);
            $user = Auth::user();
            $usdTotal = 0.0;
            foreach ($user->walletAccounts()->with('balances.asset')->get() as $wallet) {
                foreach ($wallet->balances as $balance) {
                    $usdTotal += ((float) $balance->available + (float) $balance->locked) * $pricing->usdPrice($balance->asset);
                }
            }
            $btc = Asset::where('symbol', 'BTC')->first();
            $btcPrice = $btc ? $pricing->usdPrice($btc) : 0;

            $view->with('topbarBalanceUsd', $usdTotal);
            $view->with('topbarBalanceBtc', $btcPrice > 0 ? $usdTotal / $btcPrice : 0);
        });
    }
}
