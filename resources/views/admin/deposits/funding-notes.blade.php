@extends('layouts.admin')
@section('title', 'Funding Notes')
@section('content')

<div class="glass-card p-4">@foreach($notes as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->user_note ?? $item->id }}</div>@endforeach</div>

@endsection
