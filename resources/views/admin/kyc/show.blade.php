@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-xl font-bold">KYC Review — {{ $submission->user->email }}</h1>

    <div class="glass-card p-5 grid gap-2 text-sm sm:grid-cols-2">
        <div><span class="text-text-muted">Legal name:</span> {{ $submission->legal_name }}</div>
        <div><span class="text-text-muted">DOB:</span> {{ $submission->date_of_birth?->format('M d, Y') }}</div>
        <div><span class="text-text-muted">Country:</span> {{ $submission->country }}</div>
        <div><span class="text-text-muted">Address:</span> {{ $submission->address }}</div>
        <div><span class="text-text-muted">ID type:</span> {{ $submission->id_type }}</div>
        <div><span class="text-text-muted">ID number:</span> {{ $submission->id_number }}</div>
        <div><span class="text-text-muted">Source of funds:</span> {{ $submission->source_of_funds }}</div>
        <div><span class="text-text-muted">Occupation:</span> {{ $submission->occupation }}</div>
        <div><span class="text-text-muted">Experience:</span> {{ $submission->trading_experience }}</div>
        <div><span class="text-text-muted">Tax residency:</span> {{ $submission->tax_residency }}</div>
        <div><span class="text-text-muted">PEP:</span> {{ $submission->is_pep ? 'Yes' : 'No' }}</div>
        <div><span class="text-text-muted">Sanctioned:</span> {{ $submission->is_sanctioned ? 'Yes' : 'No' }}</div>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-2 font-semibold">Documents</h2>
        <p class="text-sm text-text-muted">Government ID, proof of address and selfie are placeholders in this simulation build (no live document viewer).</p>
    </div>

    @if (in_array($submission->status, ['submitted', 'under_review', 'more_info_required']))
    <div class="glass-card p-5 space-y-3">
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.kyc.approve', $submission) }}">@csrf<button class="btn-brand text-sm">Approve</button></form>
        </div>
        <form method="POST" action="{{ route('admin.kyc.reject', $submission) }}" class="flex gap-2">
            @csrf
            <input type="text" name="reason" class="input-field" placeholder="Rejection reason" required>
            <button class="text-sm text-danger hover:underline">Reject</button>
        </form>
        <form method="POST" action="{{ route('admin.kyc.more-info', $submission) }}" class="flex gap-2">
            @csrf
            <input type="text" name="reason" class="input-field" placeholder="What info is needed?" required>
            <button class="text-sm text-brand hover:underline">Request More Info</button>
        </form>
    </div>
    @endif
</div>
@endsection
