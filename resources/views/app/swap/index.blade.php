@extends('layouts.app-shell')
@section('title', 'Swap')
@section('content')

<h1 class="text-2xl font-bold mb-6">Crypto Swap</h1>
<form method="POST" action="{{ route('app.swap.execute') }}" class="glass-card max-w-lg space-y-4 p-6">@csrf
<label class="label-field">From asset<select name="from_asset_id" class="input-field">@foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->symbol }}</option>@endforeach</select></label>
<label class="label-field">To asset<select name="to_asset_id" class="input-field">@foreach($assets as $a)<option value="{{ $a->id }}" @selected($a->symbol==='USDT')>{{ $a->symbol }}</option>@endforeach</select></label>
<label class="label-field">From wallet<select name="from_wallet" class="input-field"><option>PRIMARY</option><option>TRADING</option><option>INVESTMENT</option></select></label>
<label class="label-field">To wallet<select name="to_wallet" class="input-field"><option>PRIMARY</option><option>TRADING</option><option>INVESTMENT</option></select></label>
<label class="label-field">Amount<input type="number" step="any" name="amount" class="input-field" required></label>
<button class="btn-brand">Execute swap</button>
</form>
<div class="glass-card mt-6 p-4"><h3 class="font-semibold mb-3">History</h3>@foreach($history as $h)<div class="text-xs font-mono border-b border-border/40 py-2">{{ $h->from_amount }} → {{ $h->to_amount }} (fee {{ $h->fee }})</div>@endforeach</div>

@endsection
