@extends('layouts.app-shell')
@section('title', 'Deposit')
@section('content')

<h1 class="text-2xl font-bold mb-6">Deposit</h1>
<form method="POST" action="{{ route('app.wallet.deposit.store') }}" class="glass-card max-w-xl space-y-4 p-6">@csrf
<label class="label-field">Destination wallet
<select name="wallet_type" class="input-field">@foreach($wallets as $w)<option value="{{ $w->type }}">{{ $w->type }}</option>@endforeach</select></label>
<label class="label-field">Asset
<select name="asset_id" class="input-field">@foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->symbol }}</option>@endforeach</select></label>
<label class="label-field">Network
<select name="network_id" class="input-field"><option value="">Select</option>@foreach($networks as $n)<option value="{{ $n->id }}">{{ $n->name }}</option>@endforeach</select></label>
<label class="label-field">Amount<input type="number" step="any" name="amount" class="input-field" required></label>
<label class="label-field">Funding note<textarea name="user_note" class="input-field" rows="2" placeholder="Optional note"></textarea></label>
<p class="risk-banner">Send only on the selected network. MVP auto-confirms simulated deposits and credits via ledger.</p>
<button class="btn-brand">Create deposit</button>
</form>

@endsection
