@extends('layouts.admin')
@section('title', 'Deposits')
@section('content')
<div class="glass-card p-4">@foreach($deposits as $d)<div class="border-b border-border/40 py-2 text-sm">{{ $d->uuid }} · user {{ $d->user_id }} · {{ $d->amount }} · {{ $d->status }}</div>@endforeach{{ $deposits->links() }}</div>
@endsection
