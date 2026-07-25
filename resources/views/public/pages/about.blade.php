@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">About Bitzlatoview</h1>
    <div class="prose prose-invert mt-6 max-w-none space-y-4 text-text-muted">
        <p>Bitzlatoview is a multi-asset trading platform bringing crypto, stocks, forex, futures and NFTs into a single, modern dashboard. Our goal is to give traders one consistent experience — the same wallet system, the same order flow patterns, and the same risk disclosures — no matter which market they're in.</p>
        <p>We are currently operating in <strong class="text-brand">simulation / paper-trading mode</strong> while we finalize the legal, compliance, broker, custody, card-issuing and KYC/AML integrations required to operate live in each jurisdiction we plan to serve. Nothing on this platform currently involves real money movement.</p>
        <p>Every balance-changing action on Bitzlatoview — deposits, withdrawals, trades, P2P releases, bot allocations, mining rewards — is recorded through a double-entry ledger. We believe an auditable ledger is non-negotiable for a platform that will eventually hold user funds.</p>
        <p>Bitzlatoview's design draws on the broad product structure common across major exchanges (spot, P2P, Earn-style products, cards, API access) purely as a UX reference — our brand, visuals and codebase are original.</p>
    </div>
</div>
@endsection
