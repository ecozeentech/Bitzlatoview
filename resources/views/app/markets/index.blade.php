@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Markets</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th></th><th>Pair</th><th>Price</th><th>24h Change</th><th>24h High</th><th>24h Low</th><th>24h Volume</th><th></th></tr></thead>
            <tbody>
                @foreach ($markets as $market)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('app.markets.watchlist', $market) }}">
                                @csrf
                                <button class="text-lg {{ in_array($market->id, $watchlist) ? 'text-brand' : 'text-text-muted' }}">★</button>
                            </form>
                        </td>
                        <td class="flex items-center gap-2"><x-asset-icon :symbol="$market->baseAsset->symbol" /> <span class="font-semibold">{{ $market->symbol }}</span></td>
                        <td class="font-numeric">${{ number_format($market->quote->price ?? 0, 4) }}</td>
                        <td><x-price-change :value="$market->quote->change_24h_pct ?? 0" /></td>
                        <td class="font-numeric text-text-muted">${{ number_format($market->quote->high_24h ?? 0, 2) }}</td>
                        <td class="font-numeric text-text-muted">${{ number_format($market->quote->low_24h ?? 0, 2) }}</td>
                        <td class="font-numeric text-text-muted">${{ number_format($market->quote->volume_24h ?? 0, 0) }}</td>
                        <td><a href="{{ url('/app/spot/'.$market->symbol) }}" class="text-sm text-brand hover:underline">Trade</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
