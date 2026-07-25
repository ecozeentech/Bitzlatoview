@extends('layouts.app-shell')
@section('title', 'Wallets')
@section('content')

<h1 class="text-2xl font-bold mb-6">Wallets</h1>
<div class="grid gap-4 md:grid-cols-3">
@foreach([['PRIMARY',$primary],['TRADING',$trading],['INVESTMENT',$investment]] as [$type,$wallet])
<div class="glass-card p-5">
  <div class="flex justify-between"><h3 class="font-semibold">{{ $type }}</h3><a href="{{ route('app.wallet.show', strtolower($type)) }}" class="text-brand text-sm">Open</a></div>
  <div class="mt-4 space-y-2">
  @forelse(($wallet->balances ?? collect()) as $b)
    <div class="flex justify-between text-sm"><span>{{ $b->asset->symbol }}</span><span class="font-mono">{{ number_format($b->available, 6) }}</span></div>
  @empty
    <p class="text-sm text-muted">No balances yet. Deposit to get started.</p>
  @endforelse
  </div>
</div>
@endforeach
</div>
<div class="mt-6 flex flex-wrap gap-3">
  <a href="{{ route('app.wallet.deposit') }}" class="btn-brand">Deposit</a>
  <a href="{{ route('app.wallet.withdraw') }}" class="btn-outline">Withdraw</a>
  <a href="{{ route('app.wallet.transfer') }}" class="btn-outline">Transfer</a>
  <a href="{{ route('app.wallet.history') }}" class="btn-ghost">History</a>
</div>

@endsection
