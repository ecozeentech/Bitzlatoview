<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->kycApproved()) {
            return redirect()
                ->route('app.settings.kyc')
                ->with('error', 'Approved KYC is required for this action.');
        }

        return $next($request);
    }
}
