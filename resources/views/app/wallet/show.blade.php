@extends('layouts.app-shell')
@section('title', 'Wallet')
@section('content')

<h1 class="text-2xl font-bold mb-2">{{ $type }} Wallet</h1>
<p class="text-sm text-muted mb-6">Available / Locked balances via double-entry ledger.</p>
<div class="glass-card overflow-x-auto p-4"><table class="data-table"><thead><tr><th>Asset</th><th>Available</th><th>Locked</th><th>Total</th><th>USD est.</th></tr></thead><tbody>
@foreach($wallet->balances as $b)
<tr>
<td>{{ $b->asset->symbol }}</td>
<td class="font-mono">{{ number_format($b->available,8) }}</td>
<td class="font-mono">{{ number_format($b->locked,8) }}</td>
<td class="font-mono">{{ number_format($b->total(),8) }}</td>
<td class="font-mono">${{ number_format($b->total() * $b->asset->mock_price_usd, 2) }}</td>
</tr>
@endforeach
</tbody></table></div>

@endsection
