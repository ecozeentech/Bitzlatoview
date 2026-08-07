@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">Proof of Reserves</h1>
    <div class="prose prose-invert mt-6 max-w-none space-y-4 text-text-muted">
        <p>Bitzlatoview intends to publish periodic proof-of-reserves attestations as the platform's custody arrangements with licensed banking/custody partners are finalized.</p>
        <p>Until an independent attestation is published, treat any reserve figures shown elsewhere on the platform as internal ledger balances rather than an audited attestation. This page will be updated with:</p>
        <ul class="list-disc space-y-2 pl-5">
            <li>A snapshot of aggregate user liabilities per asset (from the internal ledger)</li>
            <li>A snapshot of custodied reserves held with our custody/banking partners</li>
            <li>An independent auditor or cryptographic attestation (e.g. Merkle-tree proof) reference</li>
        </ul>
    </div>
</div>
@endsection
