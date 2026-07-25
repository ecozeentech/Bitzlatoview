@extends('layouts.app-shell')
@section('title', 'P2P Ads')
@section('content')

<h1 class="text-2xl font-bold mb-6">My P2P Ads</h1>
<form method="POST" action="{{ route('app.p2p.ads.store') }}" class="glass-card mb-6 grid gap-3 p-5 md:grid-cols-2">@csrf
<select name="asset_id" class="input-field">@foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->symbol }}</option>@endforeach</select>
<select name="side" class="input-field"><option value="sell">Sell</option><option value="buy">Buy</option></select>
<input name="fiat_currency" class="input-field" value="USD" maxlength="3">
<input name="price" class="input-field" placeholder="Price" type="number" step="any" required>
<input name="available_amount" class="input-field" placeholder="Available" type="number" step="any" required>
<input name="min_limit" class="input-field" placeholder="Min" type="number" step="any" required>
<input name="max_limit" class="input-field" placeholder="Max" type="number" step="any" required>
<textarea name="terms" class="input-field md:col-span-2" placeholder="Terms"></textarea>
<button class="btn-brand md:col-span-2">Create ad</button>
</form>
<div class="glass-card p-4">@foreach($ads as $ad)<div class="border-b border-border/40 py-3 text-sm">{{ $ad->side }} {{ $ad->asset_id }} @ {{ $ad->price }} · {{ $ad->status }}</div>@endforeach</div>

@endsection
