@extends('layouts.admin')
@section('title', 'Swap')
@section('content')

<div class="glass-card p-4">@foreach($swaps as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->uuid ?? $item->id }}</div>@endforeach</div>

@endsection
