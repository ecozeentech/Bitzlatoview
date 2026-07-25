@extends('layouts.admin')
@section('title', 'Virtual Cards')
@section('content')

<div class="glass-card p-4">@foreach($cards as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->masked_pan ?? $item->id }}</div>@endforeach</div>

@endsection
