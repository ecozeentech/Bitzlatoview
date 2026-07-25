@extends('layouts.admin')
@section('title', 'Stocks')
@section('content')

<div class="glass-card p-4">@foreach($stocks as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->symbol ?? $item->id }}</div>@endforeach</div>

@endsection
