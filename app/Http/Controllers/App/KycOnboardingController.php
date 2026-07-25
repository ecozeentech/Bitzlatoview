<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KycSubmission;
use App\Models\RiskScore;
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

    public function store(Request $request)
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
        ]);

        $data['is_pep'] = $request->boolean('is_pep');
        $data['is_sanctioned'] = $request->boolean('is_sanctioned');

        $submission = KycSubmission::create($data + [
            'user_id' => $user->id,
            'government_id_path' => 'placeholder/government-id.pdf',
            'proof_of_address_path' => 'placeholder/proof-of-address.pdf',
            'selfie_path' => 'placeholder/selfie.jpg',
            'status' => 'under_review',
            'submitted_at' => now(),
        ]);

        RiskScore::create([
            'user_id' => $user->id,
            'score' => ($data['is_pep'] || $data['is_sanctioned']) ? 80 : 20,
            'level' => ($data['is_pep'] || $data['is_sanctioned']) ? 'high' : 'low',
            'factors' => ['pep' => $data['is_pep'], 'sanctioned' => $data['is_sanctioned']],
        ]);

        $user->forceFill(['kyc_status' => 'under_review'])->save();

        AuditLog::record($user, 'kyc.submitted', KycSubmission::class, $submission->id, null, ['status' => 'under_review']);

        // Simulation-mode auto-approval: no sanctions/PEP flags and no linked admin review queue backlog.
        if (! $data['is_pep'] && ! $data['is_sanctioned']) {
            $submission->update(['status' => 'approved', 'reviewed_at' => now()]);
            $user->forceFill(['kyc_status' => 'approved'])->save();
            AuditLog::record($user, 'kyc.auto_approved', KycSubmission::class, $submission->id);

            return redirect('/app/dashboard')->with('success', 'Identity verification approved. All features are now unlocked (simulation mode).');
        }

        return redirect('/app/dashboard')->with('success', 'Your verification has been submitted and is under manual compliance review.');
    }
}
