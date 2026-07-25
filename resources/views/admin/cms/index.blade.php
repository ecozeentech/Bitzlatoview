@extends('layouts.admin')
@section('title', 'CMS')
@section('content')

<div class="glass-card p-4">@foreach($pages as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->title ?? $item->id }}</div>@endforeach</div>

@endsection
