@extends('layouts.admin')
@section('title', 'Fees')
@section('content')

<div class="glass-card p-4">@foreach($fees as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->name ?? $item->id }}</div>@endforeach</div>

@endsection
