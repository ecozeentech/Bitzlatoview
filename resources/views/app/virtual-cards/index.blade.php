@extends('layouts.app-shell')
@section('title', 'Virtual Cards')
@section('content')

<h1 class="text-2xl font-bold mb-2">Virtual Cards</h1>
<p class="risk-banner mb-6">PROVIDER: Stripe Issuing / Marqeta / Lithic. Full PAN never stored; mock cards only in MVP.</p>
@if(!$kycApproved)<a href="{{ route('app.settings.kyc') }}" class="badge-warning mb-4 inline-flex">KYC required</a>@endif
<form method="POST" action="{{ route('app.virtual-cards.create') }}" class="glass-card mb-6 grid gap-3 p-5 md:grid-cols-3">@csrf
<input name="nickname" class="input-field" placeholder="Nickname">
<input name="spending_limit" type="number" step="any" class="input-field" placeholder="Limit" required>
<input name="currency" class="input-field" value="USD" maxlength="3">
<button class="btn-brand md:col-span-3">Create virtual card</button>
</form>
<div class="grid gap-4 md:grid-cols-2">
@foreach($cards as $card)
<div class="glass-card relative overflow-hidden p-6">
  <div class="absolute inset-0 bg-gradient-to-br from-brand/20 via-transparent to-info/10"></div>
  <div class="relative"><p class="text-xs uppercase tracking-widest text-brand-soft">{{ $card->brand }}</p>
  <p class="mt-6 font-mono text-xl tracking-[0.2em]">{{ $card->masked_pan }}</p>
  <p class="mt-4 text-sm">{{ $card->nickname }} · {{ $card->status }}</p>
  <p class="text-xs text-muted mt-1">Limit ${{ number_format($card->spending_limit,2) }} · Spent ${{ number_format($card->spent_amount,2) }}</p>
  <div class="mt-4 flex gap-2">
  <form method="POST" action="{{ route('app.virtual-cards.freeze',$card->id) }}">@csrf<button class="btn-outline text-xs">{{ $card->status==='frozen'?'Unfreeze':'Freeze' }}</button></form>
  <a href="{{ route('app.virtual-cards.transactions',$card->id) }}" class="btn-ghost text-xs">Transactions</a>
  </div></div>
</div>
@endforeach
</div>

@endsection
