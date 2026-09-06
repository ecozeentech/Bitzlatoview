<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RequiresTwoFactor;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KycReview;
use App\Models\KycSubmission;
use App\Models\User;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    use RequiresTwoFactor;

    /**
     * Stream a KYC document from the private disk. Never publicly reachable — only an
     * authenticated admin can view it via this route.
     */
    public function document(KycSubmission $submission, string $field)
    {
        abort_unless(in_array($field, ['government_id_path', 'proof_of_address_path', 'selfie_path']), 404);

        $path = $submission->{$field};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function index()
    {
        $submissions = KycSubmission::with('user')->whereIn('status', ['submitted', 'under_review', 'more_info_required'])->latest()->paginate(20);
        $recentlyDecided = KycSubmission::with('user')->whereIn('status', ['approved', 'rejected'])->latest('reviewed_at')->take(10)->get();

        return view('admin.kyc.index', compact('submissions', 'recentlyDecided'));
    }

    public function show(KycSubmission $submission)
    {
        $submission->load('user', 'reviews');

        return view('admin.kyc.show', compact('submission'));
    }

    public function approve(KycSubmission $submission, TransactionalMailService $mailer)
    {
        $submission->update(['status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $submission->user->forceFill(['kyc_status' => 'approved'])->save();

        KycReview::create(['kyc_submission_id' => $submission->id, 'reviewer_id' => auth()->id(), 'decision' => 'approved']);
        AuditLog::record(auth()->user(), 'kyc.approved', KycSubmission::class, $submission->id);
        $mailer->send($submission->user, 'kyc_approved', ['name' => $submission->user->name]);

        return back()->with('success', 'KYC approved.');
    }

    public function reject(Request $request, KycSubmission $submission, TransactionalMailService $mailer)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $submission->update(['status' => 'rejected', 'rejection_reason' => $data['reason'], 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $submission->user->forceFill(['kyc_status' => 'rejected'])->save();

        KycReview::create(['kyc_submission_id' => $submission->id, 'reviewer_id' => auth()->id(), 'decision' => 'rejected', 'notes' => $data['reason']]);
        AuditLog::record(auth()->user(), 'kyc.rejected', KycSubmission::class, $submission->id);
        $mailer->send($submission->user, 'kyc_rejected', ['name' => $submission->user->name, 'reason' => $data['reason']]);

        return back()->with('success', 'KYC rejected.');
    }

    public function moreInfo(Request $request, KycSubmission $submission)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $submission->update(['status' => 'more_info_required', 'rejection_reason' => $data['reason'], 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $submission->user->forceFill(['kyc_status' => 'more_info_required'])->save();

        KycReview::create(['kyc_submission_id' => $submission->id, 'reviewer_id' => auth()->id(), 'decision' => 'more_info_required', 'notes' => $data['reason']]);

        return back()->with('success', 'Requested more information from the user.');
    }

    /**
     * Admin can activate KYC for a user directly, without requiring them to upload any
     * documents — e.g. for a trusted user verified through another channel, or to unblock
     * someone while document review is still pending. Works whether or not the user has
     * ever submitted a KycSubmission at all. Always requires a written reason for the audit
     * trail, and (if the admin has 2FA enabled) a valid current 2FA code.
     */
    public function manuallyApprove(Request $request, User $user, TransactionalMailService $mailer)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($error = $this->verifyTwoFactor($request, Auth::user())) {
            return back()->with('error', $error);
        }

        $submission = KycSubmission::firstOrCreate(['user_id' => $user->id], ['status' => 'not_started']);

        $submission->update([
            'status' => 'approved',
            'manually_verified' => true,
            'manual_approval_reason' => $data['reason'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        $user->forceFill(['kyc_status' => 'approved'])->save();

        KycReview::create([
            'kyc_submission_id' => $submission->id,
            'reviewer_id' => Auth::id(),
            'decision' => 'approved',
            'notes' => "Manually approved without document upload: {$data['reason']}",
        ]);

        AuditLog::record(Auth::user(), 'kyc.manually_approved', KycSubmission::class, $submission->id, null, [
            'user_id' => $user->id,
            'reason' => $data['reason'],
        ]);

        $mailer->send($user, 'kyc_approved', ['name' => $user->name]);

        return back()->with('success', "KYC manually approved for {$user->email} — no document upload was required.");
    }
}
