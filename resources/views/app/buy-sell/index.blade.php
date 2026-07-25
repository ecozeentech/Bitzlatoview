@extends('layouts.app-shell')
@section('title', 'Buy / Sell')
@section('content')

<h1 class="text-2xl font-bold mb-6">Quick Buy / Sell</h1>
<form method="POST" action="{{ route('app.buy-sell.execute') }}" class="glass-card max-w-lg space-y-4 p-6">@csrf
<label class="label-field">Pair<select name="pair_id" class="input-field">@foreach($pairs as $p)<option value="{{ $p->id }}">{{ $p->symbol }}</option>@endforeach</select></label>
<label class="label-field">Side<select name="side" class="input-field"><option value="buy">Buy</option><option value="sell">Sell</option></select></label>
<label class="label-field">Quantity<input type="number" step="any" name="quantity" class="input-field" required></label>
<button class="btn-brand">Confirm market order</button>
</form>

@endsection
