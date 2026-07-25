<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\AuditLog;
use App\Models\ConnectedWallet;
use App\Models\DeviceSession;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function profile()
    {
        return view('app.settings.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $user->update($data);
        AuditLog::record($user, 'profile.updated');

        return back()->with('success', 'Profile updated.');
    }

    public function security()
    {
        $user = Auth::user();
        $sessions = DeviceSession::where('user_id', $user->id)->latest('last_seen_at')->get();
        $totp = new TotpService;
        $pendingSecret = session('2fa_pending_secret') ?? $totp->generateSecret();
        session(['2fa_pending_secret' => $pendingSecret]);

        return view('app.settings.security', [
            'sessions' => $sessions,
            'pendingSecret' => $pendingSecret,
            'provisioningUri' => (new TotpService)->provisioningUri($pendingSecret, $user->email),
        ]);
    }

    public function enable2fa(Request $request, TotpService $totp)
    {
        $user = Auth::user();
        $secret = session('2fa_pending_secret');

        $data = $request->validate(['code' => ['required', 'string']]);

        if (! $secret || ! $totp->verify($secret, $data['code'])) {
            return back()->with('error', 'Invalid authentication code. Please try again.');
        }

        $user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt($secret),
        ])->save();

        session()->forget('2fa_pending_secret');
        AuditLog::record($user, '2fa.enabled');

        return back()->with('success', 'Two-factor authentication enabled.');
    }

    public function disable2fa()
    {
        $user = Auth::user();
        $user->forceFill(['two_factor_enabled' => false, 'two_factor_secret' => null])->save();
        AuditLog::record($user, '2fa.disabled');

        return back()->with('success', 'Two-factor authentication disabled.');
    }

    public function kyc()
    {
        return redirect()->route('kyc-onboarding');
    }

    public function submitKyc()
    {
        return redirect()->route('kyc-onboarding');
    }

    public function notifications()
    {
        return view('app.settings.notifications');
    }

    public function updateNotifications(Request $request)
    {
        // Notification preferences are stored client-side/placeholder in this MVP build.
        return back()->with('success', 'Notification preferences saved.');
    }

    public function apiKeys()
    {
        $keys = ApiKey::where('user_id', Auth::id())->whereNull('revoked_at')->get();

        return view('app.settings.api-keys', compact('keys'));
    }

    public function generateApiKey(Request $request)
    {
        $data = $request->validate(['label' => ['nullable', 'string', 'max:100']]);

        $secret = Str::random(32);

        $key = ApiKey::create([
            'user_id' => Auth::id(),
            'label' => $data['label'] ?? 'Default API Key',
            'key' => 'bzv_'.Str::random(24),
            'secret_last_four' => substr($secret, -4),
            'permissions' => ['read'],
        ]);

        AuditLog::record(Auth::user(), 'api_key.generated', ApiKey::class, $key->id);

        return back()->with('success', 'API key generated: '.$key->key.' (secret: '.$secret.' — shown only once, store it securely).');
    }

    public function revokeApiKey(Request $request)
    {
        $data = $request->validate(['id' => ['required', 'exists:api_keys,id']]);
        $key = ApiKey::where('id', $data['id'])->where('user_id', Auth::id())->firstOrFail();
        $key->update(['revoked_at' => now()]);

        return back()->with('success', 'API key revoked.');
    }

    public function walletConnect()
    {
        $wallets = ConnectedWallet::where('user_id', Auth::id())->get();

        return view('app.settings.wallet-connect', compact('wallets'));
    }

    public function connectWallet(Request $request)
    {
        $data = $request->validate([
            'provider' => ['required', 'in:walletconnect,metamask,trust,coinbase,rainbow,ledger'],
            'address' => ['required', 'string', 'max:100'],
            'chain' => ['nullable', 'string', 'max:50'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        ConnectedWallet::create($data + ['user_id' => Auth::id(), 'chain' => $data['chain'] ?? 'ethereum', 'connected_at' => now()]);

        AuditLog::record(Auth::user(), 'wallet_connect.connected');

        return back()->with('success', 'Wallet connected. Remember: an external wallet balance is separate from your Bitzlatoview custodial balance.');
    }

    public function disconnectWallet(int $id)
    {
        ConnectedWallet::where('id', $id)->where('user_id', Auth::id())->delete();

        return back()->with('success', 'Wallet disconnected.');
    }
}
