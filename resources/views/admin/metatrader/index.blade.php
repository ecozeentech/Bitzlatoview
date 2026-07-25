@extends('layouts.admin')
@section('title', 'MetaTrader')
@section('content')

<div class="glass-card p-4">@foreach($accounts as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->broker_name ?? $item->id }}</div>@endforeach</div>

@endsection
