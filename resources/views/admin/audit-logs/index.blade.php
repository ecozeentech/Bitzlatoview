@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('content')

<div class="glass-card p-4">@foreach($logs as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->action ?? $item->id }}</div>@endforeach</div>

@endsection
