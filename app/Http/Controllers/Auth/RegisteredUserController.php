<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TransactionalMailService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, TransactionalMailService $mailer): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:32'],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
            'terms' => ['accepted'],
            'privacy' => ['accepted'],
            'risk' => ['accepted'],
        ]);

        $referrer = $request->referral_code
            ? User::where('referral_code', $request->referral_code)->first()
            : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'city' => $request->city,
            'password' => Hash::make($request->password),
            'referred_by' => $referrer?->id,
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
            'risk_disclosure_accepted_at' => now(),
        ]);

        event(new Registered($user));

        $mailer->send($user, 'welcome', ['name' => $user->name]);

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
