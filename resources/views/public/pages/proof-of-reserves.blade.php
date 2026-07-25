@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">Proof of Reserves</h1>
    <div class="prose prose-invert mt-6 max-w-none space-y-4 text-text-muted">
        <p>Bitzlatoview intends to publish periodic proof-of-reserves attestations once the platform moves out of simulation mode and begins custodying real user funds through licensed custody partners.</p>
        <p>Because the platform is currently running in <strong class="text-brand">simulation / paper-trading mode</strong>, there are no real reserves to attest to yet. This page will be updated with:</p>
        <ul class="list-disc space-y-2 pl-5">
            <li>A snapshot of aggregate user liabilities per asset (from the internal ledger)</li>
            <li>A snapshot of custodied reserves held with our custody/banking partners</li>
            <li>An independent auditor or cryptographic attestation (e.g. Merkle-tree proof) reference</li>
        </ul>
    </div>
</div>
@endsection
