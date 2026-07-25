@extends('layouts.admin')
@section('title', 'Compliance')
@section('content')

<div class="glass-card p-4">@foreach($alerts as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->alert_type ?? $item->id }}</div>@endforeach</div>

@endsection
