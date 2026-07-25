@extends('layouts.app-shell')
@section('title', 'Appeals')
@section('content')

<h1 class="text-2xl font-bold mb-6">Appeals</h1>
@forelse($orders as $order)
<div class="glass-card mb-3 p-4 text-sm">Order {{ $order->uuid }} · {{ $order->status }}</div>
@empty<p class="text-muted">No open appeals.</p>@endforelse

@endsection
