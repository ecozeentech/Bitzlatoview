@extends('layouts.app-shell')
@section('title', 'Card Transactions')
@section('content')

<h1 class="text-2xl font-bold mb-6">Card {{ $card->masked_pan }}</h1>
@forelse($transactions as $t)<div class="glass-card mb-2 p-3 text-sm">{{ $t->merchant_name }} · {{ $t->amount }} {{ $t->currency }} · {{ $t->status }}</div>@empty<p class="text-muted">No transactions yet.</p>@endforelse

@endsection
