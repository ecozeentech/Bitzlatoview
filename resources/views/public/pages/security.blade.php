@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">Security</h1>
    <div class="prose prose-invert mt-6 max-w-none space-y-4 text-text-muted">
        <p>Bitzlatoview is built around the following security principles:</p>
        <ul class="list-disc space-y-2 pl-5">
            <li><strong class="text-text-main">Double-entry ledger:</strong> every balance change is a matched debit/credit pair — balances are never mutated directly.</li>
            <li><strong class="text-text-main">No direct balance edits:</strong> admin balance adjustments require a maker/checker approval flow with a documented reason and evidence.</li>
            <li><strong class="text-text-main">KYC/AML gates:</strong> withdrawals, virtual cards, futures, forex, stock trading and P2P merchant status require an approved identity verification.</li>
            <li><strong class="text-text-main">Two-factor authentication:</strong> available for login and required for high-risk actions such as withdrawals and security changes.</li>
            <li><strong class="text-text-main">Full audit logging:</strong> every sensitive admin and user action is logged with actor, IP address, and before/after state.</li>
            <li><strong class="text-text-main">Idempotency keys:</strong> financial operations use idempotency keys to prevent duplicate processing on retries.</li>
            <li><strong class="text-text-main">Withdrawal address whitelisting:</strong> new withdrawal addresses go through a cooldown period before first use.</li>
        </ul>
        <p>Bitzlatoview does not currently custody real user funds pending completion of licensed custody, banking and compliance partnerships. See our <a href="/proof-of-reserves" class="text-brand hover:underline">Proof of Reserves</a> page for details once live custody begins.</p>
    </div>
</div>
@endsection
