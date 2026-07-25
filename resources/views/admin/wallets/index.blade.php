@extends('layouts.admin')
@section('title', 'Wallets')
@section('content')

<div class="glass-card p-4">@foreach($users as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->email ?? $item->id }}</div>@endforeach</div>

@endsection
