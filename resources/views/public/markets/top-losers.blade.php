@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold">Top Losers</h1>
        <p class="mt-2 text-text-muted">The weakest 24h performers (simulated data).</p>
        <div class="mt-4 flex gap-3 text-sm">
            <a href="/markets" class="nav-link">All Markets</a>
            <a href="/markets/top-gainers" class="nav-link">Top Gainers</a>
            <a href="/markets/top-losers" class="pill-warning">Top Losers</a>
            <a href="/markets/new-listings" class="nav-link">New Listings</a>
        </div>
    </div>

    <h2 class="mb-3 font-semibold">Crypto</h2>
    @include('public.partials.market-table', ['markets' => $markets])

    <h2 class="mb-3 mt-8 font-semibold">Stocks</h2>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Symbol</th><th>Name</th><th>Price</th><th>Change</th></tr></thead>
            <tbody>
                @foreach ($stocks as $stock)
                    <tr>
                        <td class="font-semibold">{{ $stock->symbol }}</td>
                        <td class="text-text-muted">{{ $stock->name }}</td>
                        <td class="font-numeric">${{ number_format($stock->last_price, 2) }}</td>
                        <td><x-price-change :value="$stock->change_pct" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
