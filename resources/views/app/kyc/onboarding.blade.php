@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-bold">Identity Verification (KYC)</h1>
    <p class="mt-2 text-sm text-text-muted">Bitzlatoview requires identity verification before unlocking withdrawals, P2P merchant tools, virtual cards, and futures/stocks/forex trading. Your documents are reviewed manually by our compliance team before approval — this is not instant.</p>

    @if ($submission)
        <div class="risk-banner mt-6">
            Current status: <strong class="text-text-main">{{ str_replace('_', ' ', $submission->status) }}</strong>
            @if ($submission->status === 'rejected' && $submission->rejection_reason)
                <br>Reason: {{ $submission->rejection_reason }}
            @endif
        </div>
    @endif

    @if (! $submission || in_array($submission->status, ['rejected', 'more_info_required']))
    <form method="POST" action="{{ route('kyc-onboarding.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label-field">Legal full name</label>
                <input name="legal_name" class="input-field" value="{{ old('legal_name', $user->name) }}" required>
            </div>
            <div>
                <label class="label-field">Date of birth</label>
                <input type="date" name="date_of_birth" class="input-field" required>
            </div>
            <div>
                <label class="label-field">Country</label>
                <input name="country" class="input-field" value="{{ old('country', $user->country) }}" required>
            </div>
            <div>
                <label class="label-field">Residential address</label>
                <input name="address" class="input-field" required>
            </div>
            <div>
                <label class="label-field">Government ID type</label>
                <select name="id_type" class="input-field" required>
                    <option value="passport">Passport</option>
                    <option value="national_id">National ID</option>
                    <option value="drivers_license">Driver's License</option>
                </select>
            </div>
            <div>
                <label class="label-field">ID number</label>
                <input name="id_number" class="input-field" required>
            </div>
            <div>
                <label class="label-field">Source of funds</label>
                <input name="source_of_funds" class="input-field" placeholder="Salary, business income, savings..." required>
            </div>
            <div>
                <label class="label-field">Occupation</label>
                <input name="occupation" class="input-field" required>
            </div>
            <div>
                <label class="label-field">Trading experience</label>
                <select name="trading_experience" class="input-field" required>
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                    <option value="professional">Professional</option>
                </select>
            </div>
            <div>
                <label class="label-field">Tax residency</label>
                <input name="tax_residency" class="input-field" value="{{ old('tax_residency', $user->country) }}" required>
            </div>
            <div>
                <label class="label-field">TIN / SSN (optional)</label>
                <input name="tin" class="input-field">
            </div>
        </div>

        <div class="glass-card space-y-3 p-4">
            <p class="text-sm font-medium">Government ID, proof of address &amp; selfie</p>
            <div>
                <label class="label-field">Government-issued ID (front, matches type selected above)</label>
                <input type="file" name="government_id" accept="image/*,.pdf" class="input-field" required>
            </div>
            <div>
                <label class="label-field">Proof of address (utility bill or bank statement, last 3 months)</label>
                <input type="file" name="proof_of_address" accept="image/*,.pdf" class="input-field" required>
            </div>
            <div>
                <label class="label-field">Selfie holding your ID</label>
                <input type="file" name="selfie" accept="image/*" class="input-field" required>
            </div>
            <p class="text-xs text-text-muted">Documents are stored securely and are only ever viewed by authorized compliance staff for verification purposes.</p>
        </div>

        <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm text-text-muted">
                <input type="checkbox" name="is_pep" value="1" class="rounded border-border bg-surface-2">
                I am a politically exposed person (PEP) or close associate of one.
            </label>
            <label class="flex items-center gap-2 text-sm text-text-muted">
                <input type="checkbox" name="is_sanctioned" value="1" class="rounded border-border bg-surface-2">
                I am subject to, or associated with, any sanctions list.
            </label>
        </div>

        <div class="risk-banner">This is not tax or legal advice. Providing false information may result in account suspension and reporting to relevant authorities.</div>

        <button type="submit" class="btn-brand">Submit Verification</button>
    </form>
    @endif
</div>
@endsection
