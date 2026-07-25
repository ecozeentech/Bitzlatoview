@extends('layouts.admin')
@section('title', 'Trades')
@section('content')
<div class="glass-card p-4">@foreach($trades as $t)<div class="border-b border-border/40 py-2 text-sm font-mono">{{ $t->side }} {{ $t->quantity }} @ {{ $t->price }}</div>@endforeach{{ $trades->links() }}</div>
@endsection
