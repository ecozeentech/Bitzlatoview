@extends('layouts.app-shell')
@section('title', 'Transfer')
@section('content')

<h1 class="text-2xl font-bold mb-6">Internal transfer</h1>
<form method="POST" action="{{ route('app.wallet.transfer.store') }}" class="glass-card max-w-xl space-y-4 p-6">@csrf
<label class="label-field">From<select name="from_wallet" class="input-field"><option>PRIMARY</option><option>TRADING</option><option>INVESTMENT</option></select></label>
<label class="label-field">To<select name="to_wallet" class="input-field"><option>TRADING</option><option>PRIMARY</option><option>INVESTMENT</option></select></label>
<label class="label-field">Asset<select name="asset_id" class="input-field">@foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->symbol }}</option>@endforeach</select></label>
<label class="label-field">Amount<input type="number" step="any" name="amount" class="input-field" required></label>
<label class="label-field">Note<textarea name="user_note" class="input-field" rows="2"></textarea></label>
<button class="btn-brand">Transfer</button>
</form>

@endsection
