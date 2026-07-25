<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\DeviceSession;
use App\Models\KycDocument;
use App\Models\KycSubmission;
use App\Models\NewsArticle;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\UserProfile;
use App\Services\AuditLogger;
use App\Services\EmailDispatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private AuditLogger $audit,
        private EmailDispatchService $email,
    ) {}

    public function index(): View
    {
        return view('app.settings.index');
    }

    public function profile(Request $request): View
    {
        $profile = UserProfile::query()->firstOrCreate(['user_id' => $request->user()->id]);

        return view('app.settings.profile', compact('profile'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:80'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $before = $user->only(['name', 'phone', 'country', 'city']);
        $user->update(collect($data)->only(['name', 'phone', 'country', 'city'])->all());
        UserProfile::query()->updateOrCreate(['user_id' => $user->id], ['bio' => $data['bio'] ?? null]);
        $this->audit->log('profile.updated', $user, $before, $user->fresh()->only(['name', 'phone', 'country', 'city']));

        return back()->with('success', 'Profile updated.');
    }

    public function security(Request $request): View
    {
        return view('app.settings.security', [
            'sessions' => DeviceSession::query()->where('user_id', $request->user()->id)->latest('last_active_at')->get(),
        ]);
    }

    public function enable2fa(Request $request): RedirectResponse
    {
        $secret = Str::upper(Str::random(16));
        $request->user()->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt($secret),
        ]);
        $this->audit->log('security.2fa_enabled', $request->user());

        return back()->with('success', '2FA enabled (MVP secret stored encrypted). Use authenticator apps in production.');
    }

    public function kyc(Request $request): View
    {
        $submission = KycSubmission::query()->where('user_id', $request->user()->id)->latest()->first();

        return view('app.settings.kyc', compact('submission'));
    }

    public function submitKyc(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'legal_name' => ['required', 'string', 'max:160'],
            'date_of_birth' => ['required', 'date'],
            'country' => ['required', 'string', 'size:2'],
            'address' => ['required', 'string', 'max:500'],
            'id_type' => ['required', 'string', 'max:60'],
            'id_number' => ['required', 'string', 'max:80'],
            'occupation' => ['nullable', 'string', 'max:120'],
            'source_of_funds' => ['nullable', 'string', 'max:120'],
            'trading_experience' => ['nullable', 'string', 'max:60'],
            'tax_residency' => ['nullable', 'string', 'size:2'],
            'tin' => ['nullable', 'string', 'max:60'],
            'is_pep' => ['nullable', 'boolean'],
            'government_id' => ['nullable', 'file', 'max:5120'],
            'proof_of_address' => ['nullable', 'file', 'max:5120'],
        ]);

        $user = $request->user();
        $submission = KycSubmission::query()->create([
            ...collect($data)->except(['government_id', 'proof_of_address'])->all(),
            'user_id' => $user->id,
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_pep' => (bool) ($data['is_pep'] ?? false),
        ]);

        foreach (['government_id', 'proof_of_address'] as $docType) {
            if ($request->hasFile($docType)) {
                // PROVIDER: Secure KYC document storage (encrypted object store).
                $path = $request->file($docType)->store('kyc/'.$user->id, 'local');
                KycDocument::query()->create([
                    'kyc_submission_id' => $submission->id,
                    'document_type' => $docType,
                    'file_path' => $path,
                    'status' => 'pending',
                ]);
            }
        }

        $user->update(['kyc_status' => 'submitted', 'full_legal_name' => $data['legal_name']]);
        $this->email->sendTemplate('kyc_submitted', $user, ['name' => $user->name]);
        $this->audit->log('kyc.submitted', $submission);

        return back()->with('success', 'KYC submitted for review.');
    }

    public function notifications(): View
    {
        return view('app.settings.notifications');
    }

    public function apiKeys(): View
    {
        return view('app.settings.api-keys');
    }

    public function support(Request $request): View
    {
        return view('app.support.index', [
            'tickets' => SupportTicket::query()->where('user_id', $request->user()->id)->latest()->get(),
        ]);
    }

    public function openTicket(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:60'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = SupportTicket::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'status' => 'open',
        ]);

        SupportMessage::query()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'is_staff' => false,
        ]);

        return back()->with('success', 'Support ticket opened.');
    }

    public function news(): View
    {
        return view('app.news.index', [
            'articles' => NewsArticle::query()->where('status', 'published')->latest('published_at')->paginate(12),
        ]);
    }

    public function blog(): View
    {
        return view('app.blog.index', [
            'posts' => BlogPost::query()->where('status', 'published')->latest('published_at')->paginate(12),
        ]);
    }

    public function referrals(Request $request): View
    {
        $user = $request->user();
        if (! $user->referral_code) {
            $user->update(['referral_code' => Str::upper(Str::random(8))]);
        }

        return view('app.referrals.index', ['code' => $user->fresh()->referral_code]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            return back()->with('error', 'Current password incorrect.');
        }

        $request->user()->update(['password' => $data['password']]);
        $this->audit->log('security.password_changed', $request->user());

        return back()->with('success', 'Password updated.');
    }
}
