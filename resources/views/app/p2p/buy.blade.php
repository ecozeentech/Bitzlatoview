@extends('layouts.app-shell')
@section('title', 'P2P Buy')
@section('content')

<h1 class="text-2xl font-bold mb-6">Buy crypto via P2P</h1>
<div class="glass-card overflow-x-auto p-4"><table class="data-table"><thead><tr><th>Merchant</th><th>Asset</th><th>Price</th><th>Limits</th><th>Available</th><th></th></tr></thead><tbody>
@foreach($ads as $ad)
<tr>
<td>{{ $ad->user->name ?? 'Merchant' }} @if($ad->merchantProfile?->is_verified)<span class="badge-success">Verified</span>@endif</td>
<td>{{ $ad->asset->symbol }}</td>
<td class="font-mono">{{ number_format($ad->price,2) }} {{ $ad->fiat_currency }}</td>
<td class="font-mono">{{ $ad->min_limit }}-{{ $ad->max_limit }}</td>
<td class="font-mono">{{ number_format($ad->available_amount,4) }}</td>
<td>
<form method="POST" action="{{ route('app.p2p.order',$ad->id) }}" class="flex gap-2">@csrf
<input type="number" step="any" name="fiat_amount" class="input-field !w-28" placeholder="Fiat" required>
<input type="text" name="payment_method" class="input-field !w-28" placeholder="Method">
<button class="btn-brand text-xs">Trade</button>
</form>
</td>
</tr>
@endforeach
</tbody></table>{{ $ads->links() }}</div>

@endsection
