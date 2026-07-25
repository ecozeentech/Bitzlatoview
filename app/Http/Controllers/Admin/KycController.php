<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KycReview;
use App\Models\KycSubmission;
use Illuminate\Http\Request;

class KycController extends Controller
{
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

    public function approve(KycSubmission $submission)
    {
        $submission->update(['status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $submission->user->forceFill(['kyc_status' => 'approved'])->save();

        KycReview::create(['kyc_submission_id' => $submission->id, 'reviewer_id' => auth()->id(), 'decision' => 'approved']);
        AuditLog::record(auth()->user(), 'kyc.approved', KycSubmission::class, $submission->id);

        return back()->with('success', 'KYC approved.');
    }

    public function reject(Request $request, KycSubmission $submission)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $submission->update(['status' => 'rejected', 'rejection_reason' => $data['reason'], 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $submission->user->forceFill(['kyc_status' => 'rejected'])->save();

        KycReview::create(['kyc_submission_id' => $submission->id, 'reviewer_id' => auth()->id(), 'decision' => 'rejected', 'notes' => $data['reason']]);
        AuditLog::record(auth()->user(), 'kyc.rejected', KycSubmission::class, $submission->id);

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
}
