@extends('layouts.app-shell')
@section('title', 'Bot Marketplace')
@section('content')

<h1 class="text-2xl font-bold mb-6">Bot marketplace</h1>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">@foreach($bots as $b)<div class="glass-card p-5"><h3 class="font-semibold">{{ $b->name }}</h3><p class="text-sm text-muted">{{ $b->description }}</p></div>@endforeach</div>

@endsection
