@extends('layouts.admin')
@section('title', 'Futures')
@section('content')

<div class="glass-card p-4">@foreach($markets as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->symbol ?? $item->id }}</div>@endforeach</div>

@endsection
