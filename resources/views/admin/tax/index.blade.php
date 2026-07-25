@extends('layouts.admin')
@section('title', 'Tax')
@section('content')

<div class="glass-card p-4">@foreach($reports as $item)<div class="border-b border-border/40 py-2 text-sm">{{ $item->tax_year ?? $item->id }}</div>@endforeach</div>

@endsection
