@extends('layouts.admin')
@section('title', 'Support')
@section('content')

<div class="glass-card p-4">@foreach($tickets as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->subject ?? $item->id }}</div>@endforeach</div>

@endsection
