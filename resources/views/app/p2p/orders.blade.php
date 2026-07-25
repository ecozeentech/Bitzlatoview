@extends('layouts.app-shell')
@section('title', 'P2P Orders')
@section('content')

<h1 class="text-2xl font-bold mb-6">P2P Orders</h1>
@foreach($orders as $order)
<div class="glass-card mb-4 p-5">
  <div class="flex flex-wrap justify-between gap-2"><div><p class="font-semibold">{{ $order->uuid }}</p><p class="text-sm text-muted">{{ $order->status }} · {{ $order->crypto_amount }} for {{ $order->fiat_amount }} {{ $order->fiat_currency }}</p></div>
  <div class="flex flex-wrap gap-2">
    @if($order->status==='awaiting_payment' && auth()->id()===$order->buyer_id)<form method="POST" action="{{ route('app.p2p.mark-paid',$order->id) }}">@csrf<button class="btn-brand text-xs">I have paid</button></form>@endif
    @if($order->status==='paid' && auth()->id()===$order->seller_id)<form method="POST" action="{{ route('app.p2p.release',$order->id) }}">@csrf<button class="btn-brand text-xs">Release crypto</button></form>@endif
    @if(in_array($order->status,['created','escrow_locked','awaiting_payment']))<form method="POST" action="{{ route('app.p2p.cancel',$order->id) }}">@csrf<button class="btn-outline text-xs">Cancel</button></form>@endif
  </div></div>
  <form method="POST" action="{{ route('app.p2p.message',$order->id) }}" class="mt-4 flex gap-2">@csrf<input name="body" class="input-field" placeholder="Chat message" required><button class="btn-outline text-xs">Send</button></form>
  <form method="POST" action="{{ route('app.p2p.appeal',$order->id) }}" class="mt-2 flex gap-2">@csrf<input name="reason" class="input-field" placeholder="Open appeal reason"><button class="btn-ghost text-xs text-danger">Appeal</button></form>
</div>
@endforeach
{{ $orders->links() }}

@endsection
