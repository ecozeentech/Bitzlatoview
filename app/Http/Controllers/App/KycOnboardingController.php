<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KycSubmission;
use App\Models\RiskScore;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycOnboardingController extends Controller
{
    public function create(Request $request)
    {
        $user = Auth::user();
        $submission = $user->latestKyc;

        return view('app.kyc.onboarding', [
            'user' => $user,
            'submission' => $submission,
        ]);
    }

    public function store(Request $request, TransactionalMailService $mailer)
    {
        $user = Auth::user();

        $data = $request->validate([
            'legal_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:-18 years'],
            'country' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'id_type' => ['required', 'in:passport,national_id,drivers_license'],
            'id_number' => ['required', 'string', 'max:100'],
            'source_of_funds' => ['required', 'string', 'max:255'],
            'occupation' => ['required', 'string', 'max:255'],
            'trading_experience' => ['required', 'in:beginner,intermediate,advanced,professional'],
            'tax_residency' => ['required', 'string', 'max:100'],
            'tin' => ['nullable', 'string', 'max:64'],
            'government_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'proof_of_address' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'selfie' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $data['is_pep'] = $request->boolean('is_pep');
        $data['is_sanctioned'] = $request->boolean('is_sanctioned');

        // Documents are stored on the private disk — never publicly reachable. Only admin
        // compliance staff can view them, via App\Http\Controllers\Admin\KycController.
        $governmentIdPath = $request->file('government_id')->store('kyc-documents/'.$user->id, 'local');
        $proofOfAddressPath = $request->file('proof_of_address')->store('kyc-documents/'.$user->id, 'local');
        $selfiePath = $request->file('selfie')->store('kyc-documents/'.$user->id, 'local');

        unset($data['government_id'], $data['proof_of_address'], $data['selfie']);

        $submission = KycSubmission::create($data + [
            'user_id' => $user->id,
            'government_id_path' => $governmentIdPath,
            'proof_of_address_path' => $proofOfAddressPath,
            'selfie_path' => $selfiePath,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        RiskScore::create([
            'user_id' => $user->id,
            'score' => ($data['is_pep'] || $data['is_sanctioned']) ? 80 : 20,
            'level' => ($data['is_pep'] || $data['is_sanctioned']) ? 'high' : 'low',
            'factors' => ['pep' => $data['is_pep'], 'sanctioned' => $data['is_sanctioned']],
        ]);

        $user->forceFill(['kyc_status' => 'submitted'])->save();

        AuditLog::record($user, 'kyc.submitted', KycSubmission::class, $submission->id, null, ['status' => 'submitted']);
        $mailer->send($user, 'kyc_submitted', ['name' => $user->name]);

        return redirect('/app/dashboard')->with('success', 'Your identity verification has been submitted and is queued for review by our compliance team. You will be notified once a decision is made — deposits, withdrawals and higher-risk features stay locked until then.');
    }
}
