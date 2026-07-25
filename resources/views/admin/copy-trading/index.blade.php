@extends('layouts.admin')
@section('title', 'Copy Trading')
@section('content')

<div class="glass-card p-4">@foreach($traders as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->display_name ?? $item->id }}</div>@endforeach</div>

@endsection
