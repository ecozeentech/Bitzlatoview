<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        if ($user->status !== 'active') {
            abort(403, 'Account suspended.');
        }

        return $next($request);
    }
}
