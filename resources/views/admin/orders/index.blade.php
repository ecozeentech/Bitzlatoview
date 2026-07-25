@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<div class="glass-card p-4">@foreach($orders as $o)<div class="border-b border-border/40 py-2 text-sm">{{ $o->uuid }} · {{ $o->side }} {{ $o->type }} {{ $o->status }}</div>@endforeach{{ $orders->links() }}</div>
@endsection
