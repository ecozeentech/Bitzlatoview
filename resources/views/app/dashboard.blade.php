@extends('layouts.app-shell')
@section('title', 'Dashboard')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
  <div><h1 class="text-2xl font-bold">Portfolio overview</h1><p class="text-sm text-muted">KYC: <span class="text-brand">{{ $user->kyc_status }}</span></p></div>
  @if($user->kyc_status !== 'approved')
  <a href="{{ route('app.settings.kyc') }}" class="badge-warning">Complete KYC to unlock withdrawals, cards, futures & merchant tools</a>
  @endif
</div>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
  @foreach([['Total portfolio',$portfolio],['Primary',$primaryUsd],['Trading',$tradingUsd],['Investment',$investmentUsd]] as [$l,$v])
  <div class="glass-card p-5"><p class="text-xs uppercase text-muted">{{ $l }}</p><p class="stat-value mt-2">${{ number_format($v, 2) }}</p></div>
  @endforeach
</div>
<div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
  @foreach([['Open orders',$openOrders],['Futures positions',$futuresPositions],['Active bots',$botAllocations],['Mining contracts',$activeMining]] as [$l,$v])
  <div class="glass-card p-4"><p class="text-xs text-muted">{{ $l }}</p><p class="mt-1 font-mono text-xl">{{ $v }}</p></div>
  @endforeach
</div>
<div class="mt-6 grid gap-6 lg:grid-cols-2">
  <div class="glass-card p-5"><h3 class="font-semibold mb-3">Top gainers</h3>@foreach($gainers as $g)<div class="flex justify-between py-2 text-sm border-b border-border/40"><span>{{ $g->symbol }}</span><span class="price-up">{{ number_format($g->change_24h,2) }}%</span></div>@endforeach</div>
  <div class="glass-card p-5"><h3 class="font-semibold mb-3">Latest news</h3>@foreach($news as $n)<div class="py-2 border-b border-border/40 text-sm">{{ $n->title }}</div>@endforeach</div>
</div>
<div class="risk-banner mt-6">Security checklist: enable 2FA, complete KYC, review withdrawal address book, and never share seed phrases. External WalletConnect balances are separate from custodial ledger balances.</div>

@endsection
