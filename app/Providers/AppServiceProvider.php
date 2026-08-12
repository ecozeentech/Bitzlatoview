<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Services\NotificationFeedService;
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
        });
    }
}
