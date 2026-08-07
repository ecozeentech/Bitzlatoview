@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold">Markets</h1>
        <p class="mt-2 text-text-muted">Live pricing across all supported crypto pairs.</p>
        <div class="mt-4 flex gap-3 text-sm">
            <a href="/markets" class="pill-warning">All Markets</a>
            <a href="/markets/top-gainers" class="nav-link">Top Gainers</a>
            <a href="/markets/top-losers" class="nav-link">Top Losers</a>
            <a href="/markets/new-listings" class="nav-link">New Listings</a>
        </div>
    </div>

    @include('public.partials.market-table', ['markets' => $markets])
</div>
@endsection
