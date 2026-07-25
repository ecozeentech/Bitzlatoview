@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">Terms of Service</h1>
    <p class="mt-2 text-sm text-text-muted">Last updated: {{ now()->format('F j, Y') }}</p>

    <div class="prose prose-invert mt-8 max-w-none space-y-5 text-text-muted">
        <p>These Terms of Service ("Terms") govern your access to and use of Bitzlatoview (the "Platform"). By creating an account or using the Platform, you agree to these Terms.</p>

        <h2 class="text-text-main">1. Simulation / Paper-Trading Mode</h2>
        <p>The Platform currently operates in simulation / paper-trading mode. No real funds are transferred, custodied, or exchanged through the Platform at this time. Balances, trades, orders, mining rewards, bot allocations, and card transactions shown on the Platform are simulated and have no real-world monetary value unless and until Bitzlatoview explicitly announces the transition to a live, licensed environment.</p>

        <h2 class="text-text-main">2. Eligibility</h2>
        <p>You must be at least 18 years old and legally capable of entering into binding contracts in your jurisdiction to use the Platform. You are responsible for ensuring your use of the Platform complies with local law.</p>

        <h2 class="text-text-main">3. Account Registration and KYC</h2>
        <p>You agree to provide accurate registration information. Certain features (withdrawals, virtual cards, futures, forex, stock trading, P2P merchant status) require completion of identity verification ("KYC") before access is granted, in line with our <a href="/aml-kyc-policy" class="text-brand hover:underline">AML/KYC Policy</a>.</p>

        <h2 class="text-text-main">4. No Guaranteed Returns</h2>
        <p>Bitzlatoview does not promise or guarantee any investment return, yield, or profit on any product, including but not limited to spot trading, futures, copy trading, AI trading bots, mining contracts, and Earn/investment products. All trading and investment activity carries risk of loss, including total loss of allocated funds.</p>

        <h2 class="text-text-main">5. Prohibited Conduct</h2>
        <p>You may not use the Platform for money laundering, terrorist financing, sanctions evasion, market manipulation, or any unlawful purpose. You may not attempt to circumvent KYC/AML controls or trade outside the escrow flow on P2P orders.</p>

        <h2 class="text-text-main">6. Fees</h2>
        <p>Fees applicable to each product are disclosed in the relevant flow before you confirm an action, and summarized on our <a href="/fees" class="text-brand hover:underline">Fees</a> page.</p>

        <h2 class="text-text-main">7. Suspension and Termination</h2>
        <p>We may suspend or terminate your account for suspected fraud, AML/compliance concerns, or violation of these Terms, subject to applicable law.</p>

        <h2 class="text-text-main">8. Limitation of Liability</h2>
        <p>To the maximum extent permitted by law, Bitzlatoview is not liable for indirect, incidental, or consequential damages arising from your use of the Platform.</p>

        <h2 class="text-text-main">9. Changes to These Terms</h2>
        <p>We may update these Terms from time to time. Continued use of the Platform after changes take effect constitutes acceptance of the revised Terms.</p>

        <h2 class="text-text-main">10. Contact</h2>
        <p>Questions about these Terms can be sent via our <a href="/contact" class="text-brand hover:underline">Contact</a> page.</p>
    </div>
</div>
@endsection
