@extends('layouts.app-shell')
@section('title', 'Investments')
@section('content')

<h1 class="text-2xl font-bold mb-6">Investment products</h1>
<a href="{{ route('app.analyst-packages') }}" class="btn-outline text-xs mb-4 inline-flex">Analyst packages</a>
<div class="grid gap-4 md:grid-cols-2">
@foreach($products as $p)
<div class="glass-card p-5"><h3 class="font-semibold">{{ $p->name }}</h3><p class="text-sm text-muted mt-2">{{ $p->description }}</p>
<p class="text-sm mt-2">Est. APY {{ $p->apy_estimate }}% · lock {{ $p->lock_days }}d</p>
<form method="POST" action="{{ route('app.investments.subscribe',$p->id) }}" class="mt-4 flex gap-2">@csrf<input type="number" step="any" name="amount" class="input-field" required><button class="btn-brand text-xs">Subscribe</button></form>
<p class="text-xs text-muted mt-2">{{ $p->risk_disclosure }}</p></div>
@endforeach
</div>

@endsection
