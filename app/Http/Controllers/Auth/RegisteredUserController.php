<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PolicyAcceptance;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\AuditLogger;
use App\Services\EmailDispatchService;
use App\Services\WalletProvisioningService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'full_legal_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:40'],
            'country' => ['required', 'string', 'size:2'],
            'city' => ['required', 'string', 'max:80'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'max:32'],
            'terms' => ['accepted'],
            'privacy' => ['accepted'],
            'risk' => ['accepted'],
        ]);

        $referredBy = null;
        if ($request->filled('referral_code')) {
            $referredBy = User::query()->where('referral_code', $request->referral_code)->value('id');
        }

        $user = User::query()->create([
            'name' => $request->full_legal_name,
            'full_legal_name' => $request->full_legal_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => strtoupper($request->country),
            'city' => $request->city,
            'password' => Hash::make($request->password),
            'referral_code' => Str::upper(Str::random(8)),
            'referred_by' => $referredBy,
            'role' => 'user',
            'status' => 'active',
            'kyc_status' => 'not_started',
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
            'risk_accepted_at' => now(),
            'email_verified_at' => now(), // MVP convenience; production should verify
        ]);

        UserProfile::query()->create(['user_id' => $user->id]);

        foreach (['terms', 'privacy', 'risk_disclosure'] as $policy) {
            PolicyAcceptance::query()->create([
                'user_id' => $user->id,
                'policy_type' => $policy,
                'version' => '1.0',
                'ip_address' => $request->ip(),
            ]);
        }

        app(WalletProvisioningService::class)->provision($user);
        app(AuditLogger::class)->log('auth.registered', $user, null, ['email' => $user->email], null, $request, $user->id);

        try {
            app(EmailDispatchService::class)->sendTemplate('welcome', $user, ['name' => $user->name]);
        } catch (\Throwable) {
            // Email adapter may be unavailable in local setup.
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('app.dashboard', absolute: false));
    }
}
