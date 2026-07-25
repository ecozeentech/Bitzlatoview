@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">Privacy Policy</h1>
    <p class="mt-2 text-sm text-text-muted">Last updated: {{ now()->format('F j, Y') }}</p>

    <div class="prose prose-invert mt-8 max-w-none space-y-5 text-text-muted">
        <h2 class="text-text-main">1. Information We Collect</h2>
        <p>We collect account information (name, email, phone, country), KYC verification data (government ID, proof of address, selfie, source of funds) when you undergo verification, transaction and usage data, and device/session data (IP address, user agent) for security purposes.</p>

        <h2 class="text-text-main">2. How We Use Your Information</h2>
        <ul class="list-disc space-y-1 pl-5">
            <li>To provide and operate the Platform's features</li>
            <li>To verify your identity and meet AML/KYC obligations</li>
            <li>To detect and prevent fraud, and to maintain audit logs of sensitive actions</li>
            <li>To send transactional emails (e.g. deposit confirmations, security alerts) and, with consent, marketing communications</li>
        </ul>

        <h2 class="text-text-main">3. Data Retention</h2>
        <p>We retain KYC and transaction records for the period required by applicable financial recordkeeping regulations, and account data for as long as your account remains active plus any additional legally required retention period.</p>

        <h2 class="text-text-main">4. Data Sharing</h2>
        <p>We do not sell your personal data. We may share data with service providers (e.g. KYC verification vendors, email providers, card issuers) strictly to provide the Platform's services, and with regulators or law enforcement where legally required.</p>

        <h2 class="text-text-main">5. Your Rights</h2>
        <p>Depending on your jurisdiction, you may have rights to access, correct, or request deletion of your personal data, subject to our regulatory recordkeeping obligations.</p>

        <h2 class="text-text-main">6. Cookies</h2>
        <p>See our <a href="/cookie-policy" class="text-brand hover:underline">Cookie Policy</a> for details on cookies and similar technologies used on the Platform.</p>

        <h2 class="text-text-main">7. Contact</h2>
        <p>For privacy-related questions, use our <a href="/contact" class="text-brand hover:underline">Contact</a> page.</p>
    </div>
</div>
@endsection
