@props(['markets'])
<div class="glass-card overflow-x-auto">
    <table class="data-table">
        <thead>
            <tr>
                <th>Pair</th>
                <th>Price</th>
                <th>24h Change</th>
                <th>24h High</th>
                <th>24h Low</th>
                <th>24h Volume</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($markets as $market)
                <tr>
                    <td>
                        <span class="flex items-center gap-2">
                            <x-asset-icon :symbol="$market->baseAsset->symbol" />
                            <span class="font-semibold">{{ $market->symbol }}</span>
                        </span>
                    </td>
                    <td class="font-numeric">${{ number_format($market->quote->price ?? 0, ($market->quote->price ?? 0) < 1 ? 4 : 2) }}</td>
                    <td><x-price-change :value="$market->quote->change_24h_pct ?? 0" /></td>
                    <td class="font-numeric text-text-muted">${{ number_format($market->quote->high_24h ?? 0, 2) }}</td>
                    <td class="font-numeric text-text-muted">${{ number_format($market->quote->low_24h ?? 0, 2) }}</td>
                    <td class="font-numeric text-text-muted">${{ number_format($market->quote->volume_24h ?? 0, 0) }}</td>
                    <td><a href="{{ route('login') }}" class="text-sm text-brand hover:underline">Trade</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-text-muted">No markets found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
