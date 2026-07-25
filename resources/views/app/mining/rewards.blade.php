@extends('layouts.app-shell')
@section('title', 'Mining Rewards')
@section('content')

<h1 class="text-2xl font-bold mb-6">Mining rewards</h1>
@foreach($rewards as $r)<div class="glass-card mb-2 p-3 text-sm font-mono">{{ $r->reward_date }} · {{ $r->amount }} · {{ $r->status }}</div>@endforeach

@endsection
