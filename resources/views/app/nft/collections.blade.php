@extends('layouts.app-shell')
@section('title', 'NFT Collections')
@section('content')

<h1 class="text-2xl font-bold mb-6">Collections</h1>
<div class="grid gap-4 md:grid-cols-3">@foreach($collections as $c)<div class="glass-card p-5"><h3 class="font-semibold">{{ $c->name }}</h3><p class="text-sm text-muted">{{ $c->description }}</p></div>@endforeach</div>

@endsection
