@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">AML / KYC Policy</h1>
    <p class="mt-2 text-sm text-text-muted">Last updated: {{ now()->format('F j, Y') }}</p>

    <div class="prose prose-invert mt-8 max-w-none space-y-5 text-text-muted">
        <p>Bitzlatoview is committed to preventing money laundering, terrorist financing, and sanctions evasion through its Know Your Customer (KYC) and Anti-Money Laundering (AML) program.</p>

        <h2 class="text-text-main">Identity Verification Tiers</h2>
        <p>Users may browse the Platform without verification. Higher-risk actions — withdrawals, virtual card issuance, futures/forex/stock trading, and P2P merchant status — require an approved KYC submission, which includes legal name, date of birth, government ID, proof of address, a selfie/liveness check, source of funds, occupation, trading experience, tax residency, and sanctions/PEP screening questions.</p>

        <h2 class="text-text-main">Ongoing Monitoring</h2>
        <p>We monitor account activity for suspicious patterns (e.g. structuring, rapid fund movement, high-risk jurisdictions) and may request additional information or place a temporary hold on an account pending review.</p>

        <h2 class="text-text-main">Sanctions &amp; PEP Screening</h2>
        <p>We screen users against sanctions and politically-exposed-person (PEP) indicators as part of onboarding and ongoing monitoring.</p>

        <h2 class="text-text-main">Recordkeeping</h2>
        <p>KYC and transaction records are retained per applicable regulatory requirements. All KYC decisions (approve/reject/more information required) are logged with reviewer identity and timestamp.</p>

        <h2 class="text-text-main">Reporting</h2>
        <p>Where legally required, we may report suspicious activity to relevant regulatory or law enforcement authorities.</p>
    </div>
</div>
@endsection
