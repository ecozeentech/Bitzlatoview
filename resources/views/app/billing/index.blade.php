@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Analyst Packages</h1>
    <div class="risk-banner">Research and commentary are for informational purposes only and do not constitute investment advice. "Analyst" credentials are labeled accurately — the CFA designation requires CFA Institute membership and is never implied unless independently verified.</div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($packages as $package)
            <div class="glass-card p-5">
                <h2 class="font-semibold">{{ $package->title }}</h2>
                <p class="mt-1 text-xs text-text-muted">By {{ $package->analyst?->name }} — {{ $package->analyst?->credential_verified ? $package->analyst->credential : ($package->analyst?->credential ?? 'Analyst') }}</p>
                <p class="mt-2 font-numeric text-2xl font-bold">${{ number_format($package->price, 0) }}<span class="text-xs text-text-muted">/{{ $package->billing_cycle }}</span></p>
                <ul class="mt-3 space-y-1 text-xs text-text-muted">
                    @foreach ($package->features ?? [] as $feature)
                        <li>✓ {{ $feature }}</li>
                    @endforeach
                </ul>
                <form method="POST" action="{{ route('app.billing.subscribe', $package) }}" class="mt-4">
                    @csrf
                    <button class="btn-brand w-full text-sm">Subscribe</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">My Subscriptions</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Package</th><th>Status</th><th>Renews</th><th></th></tr></thead>
            <tbody>
                @forelse ($subscriptions as $s)
                    <tr>
                        <td>{{ $s->package->title }}</td>
                        <td><span class="pill-{{ $s->status === 'active' ? 'success' : 'muted' }}">{{ $s->status }}</span></td>
                        <td class="text-text-muted">{{ $s->renews_at?->format('M d, Y') }}</td>
                        <td>@if ($s->status === 'active')<form method="POST" action="{{ route('app.billing.cancel', $s) }}">@csrf<button class="text-xs text-danger hover:underline">Cancel</button></form>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-text-muted">No subscriptions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Invoices</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
            <thead><tr><th>Invoice #</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr>
                        <td>{{ $inv->invoice_number }}</td>
                        <td class="font-numeric">${{ number_format($inv->amount, 2) }}</td>
                        <td><span class="pill-success">{{ $inv->status }}</span></td>
                        <td class="text-text-muted">{{ $inv->issued_at->format('M d, Y') }}</td>
                        <td><a href="{{ route('app.billing.invoice', $inv) }}" class="text-xs text-brand hover:underline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
