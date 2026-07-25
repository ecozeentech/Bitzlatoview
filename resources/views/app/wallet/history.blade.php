@extends('layouts.app-shell')
@section('title', 'Wallet History')
@section('content')

<h1 class="text-2xl font-bold mb-6">Funding history</h1>
<div class="grid gap-6 lg:grid-cols-3">
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Deposits</h3>@foreach($deposits as $d)<div class="border-b border-border/40 py-2 text-xs font-mono">{{ $d->status }} · {{ $d->amount }}</div>@endforeach</div>
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Withdrawals</h3>@foreach($withdrawals as $w)<div class="border-b border-border/40 py-2 text-xs font-mono">{{ $w->status }} · {{ $w->amount }}</div>@endforeach</div>
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Transfers</h3>@foreach($transfers as $t)<div class="border-b border-border/40 py-2 text-xs font-mono">{{ $t->amount }} · {{ $t->status }}</div>@endforeach</div>
</div>

@endsection
