@extends('layouts.app-shell')
@section('title', 'My Mining')
@section('content')

<h1 class="text-2xl font-bold mb-6">My mining contracts</h1>
@foreach($contracts as $c)<div class="glass-card mb-3 p-4 text-sm">{{ $c->miningPackage->name ?? 'Package' }} · {{ $c->status }} · ends {{ $c->ends_at }}</div>@endforeach

@endsection
