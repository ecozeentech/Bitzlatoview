@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Analyst Packages &amp; Credentials</h1>
    <div class="risk-banner">Only mark a credential verified if the analyst is an actual verified charterholder/professional and the platform has legal permission to display the designation (e.g. CFA requires CFA Institute membership).</div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Analysts</h2>
        <table class="data-table">
            <thead><tr><th>Name</th><th>Credential</th><th>Verified</th><th></th></tr></thead>
            <tbody>
                @foreach ($analysts as $a)
                    <tr>
                        <td>{{ $a->name }}</td>
                        <td>{{ $a->credential }}</td>
                        <td><span class="pill-{{ $a->credential_verified ? 'success' : 'warning' }}">{{ $a->credential_verified ? 'Verified' : 'Unverified' }}</span></td>
                        <td>@unless($a->credential_verified)<form method="POST" action="{{ route('admin.billing.analysts.verify', $a) }}">@csrf<button class="text-xs text-brand hover:underline">Mark Verified</button></form>@endunless</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Create Package</h2>
        <form method="POST" action="{{ route('admin.billing.store') }}" class="grid gap-3 sm:grid-cols-3">
            @csrf
            <input type="text" name="title" class="input-field" placeholder="Title" required>
            <select name="analyst_profile_id" class="input-field">@foreach ($analysts as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select>
            <input type="number" step="0.01" name="price" class="input-field" placeholder="Price" required>
            <select name="billing_cycle" class="input-field"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="yearly">Yearly</option></select>
            <input type="text" name="invoice_label" class="input-field sm:col-span-2" placeholder="Invoice label" required>
            <button class="btn-brand sm:col-span-3">Create Package</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Package</th><th>Price</th><th>Subscribers</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($packages as $p)
                    <tr>
                        <td>{{ $p->title }}</td>
                        <td class="font-numeric">${{ number_format($p->price, 2) }}/{{ $p->billing_cycle }}</td>
                        <td class="font-numeric">{{ $p->subscriptions_count }}</td>
                        <td><span class="pill-{{ $p->status === 'active' ? 'success' : 'muted' }}">{{ $p->status }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.billing.update', $p) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $p->status === 'active' ? 'archived' : 'active' }}">
                                <button class="text-xs text-brand hover:underline">{{ $p->status === 'active' ? 'Archive' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
