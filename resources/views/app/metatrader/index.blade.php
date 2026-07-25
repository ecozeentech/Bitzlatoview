@extends('layouts.app-shell')
@section('title', 'MetaTrader 5')
@section('content')

<h1 class="text-2xl font-bold mb-2">MetaTrader 5 / Meta Trading</h1>
<p class="text-sm text-muted mb-6">PROVIDER: Prefer broker OAuth/API. Never store MT5 passwords in plain text. Web terminal requires licensing.</p>
<form method="POST" action="{{ route('app.metatrader.connect') }}" class="glass-card mb-6 grid gap-3 p-5 md:grid-cols-2">@csrf
<input name="broker_name" class="input-field" placeholder="Broker name" required>
<input name="mt5_login" class="input-field" placeholder="MT5 login" required>
<input name="server_name" class="input-field" placeholder="Server" required>
<select name="account_type" class="input-field"><option value="demo">Demo</option><option value="live">Live</option></select>
<input name="leverage" type="number" class="input-field" value="100">
<input name="currency" class="input-field" value="USD" maxlength="3">
<button class="btn-brand md:col-span-2">Connect (simulation)</button>
</form>
<div class="grid gap-4 md:grid-cols-2">
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Accounts</h3>@foreach($accounts as $a)<div class="text-sm border-b border-border/40 py-2">{{ $a->broker_name }} · {{ $a->mt5_login }} @ {{ $a->server_name }}</div>@endforeach</div>
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Positions</h3>@foreach($positions as $p)<div class="text-sm border-b border-border/40 py-2">{{ $p->symbol }} {{ $p->side }} {{ $p->volume }}</div>@endforeach</div>
</div>

@endsection
