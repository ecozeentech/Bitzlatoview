<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active UI locale: the logged-in user's saved preference, else the session
 * value (set by the language switcher for guests), else the app default. Only affects static
 * UI text via lang/ files — never touches stored data, which stays in its original language.
 */
class SetLocale
{
    public const SUPPORTED = ['en', 'es', 'fr', 'ar', 'zh'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        if (Auth::check() && Auth::user()->locale && in_array(Auth::user()->locale, self::SUPPORTED, true)) {
            $locale = Auth::user()->locale;
        } elseif (session('locale') && in_array(session('locale'), self::SUPPORTED, true)) {
            $locale = session('locale');
        }

        App::setLocale($locale ?? config('app.locale'));

        return $next($request);
    }
}
