@extends('layouts.app-shell')
@section('title', 'Analyst Packages')
@section('content')

<h1 class="text-2xl font-bold mb-2">Verified Financial Analyst Packages</h1>
<p class="risk-banner mb-6">CFA designation is only shown when credential_verified is true for an actual charterholder. Not investment advice.</p>
<div class="grid gap-4 md:grid-cols-2">
@foreach($packages as $p)
<div class="glass-card p-5"><h3 class="font-semibold">{{ $p->title }}</h3>
<p class="text-sm text-muted mt-2">{{ $p->description }}</p>
<p class="mt-2 text-sm">{{ $p->analyst_name }} @if($p->credential_verified)<span class="badge-success">{{ $p->analyst_credential }}</span>@endif</p>
<p class="font-mono mt-2">${{ number_format($p->price,2) }} / {{ $p->billing_cycle }}</p>
<form method="POST" action="{{ route('app.analyst-packages.purchase',$p->id) }}" class="mt-4">@csrf<button class="btn-brand text-xs">Purchase</button></form></div>
@endforeach
</div>

@endsection
