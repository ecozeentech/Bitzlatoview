@extends('layouts.admin')
@section('title', 'Settings')
@section('content')

<div class="glass-card p-4">@foreach($flags as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->key ?? $item->id }}</div>@endforeach</div>

@endsection
