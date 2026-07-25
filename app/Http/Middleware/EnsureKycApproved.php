<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate high-risk actions (withdrawals, card issuance, futures, P2P merchant status)
 * behind an approved KYC status, per compliance requirements.
 */
class EnsureKycApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->kyc_status !== 'approved') {
            return redirect('/app/settings/kyc')
                ->with('error', 'This action requires an approved identity verification (KYC). Please complete verification to continue.');
        }

        return $next($request);
    }
}
