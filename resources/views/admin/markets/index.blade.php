@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Markets &amp; Assets</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Pair</th><th>Price</th><th>24h %</th><th>Maker Fee</th><th>Taker Fee</th><th>Active</th><th></th></tr></thead>
            <tbody>
                @foreach ($pairs as $pair)
                    <tr>
                        <td>{{ $pair->symbol }}</td>
                        <td class="font-numeric">${{ number_format($pair->quote->price ?? 0, 4) }}</td>
                        <td><x-price-change :value="$pair->quote->change_24h_pct ?? 0" /></td>
                        <td>{{ $pair->maker_fee_pct }}%</td>
                        <td>{{ $pair->taker_fee_pct }}%</td>
                        <td><span class="pill-{{ $pair->is_active ? 'success' : 'muted' }}">{{ $pair->is_active ? 'Active' : 'Paused' }}</span></td>
                        <td class="space-y-1">
                            <form method="POST" action="{{ route('admin.markets.update', $pair) }}" class="flex items-center gap-1">
                                @csrf @method('PATCH')
                                <input type="number" step="0.0001" name="price" class="input-field w-24 text-xs" placeholder="Set price">
                                <input type="hidden" name="maker_fee_pct" value="{{ $pair->maker_fee_pct }}">
                                <input type="hidden" name="taker_fee_pct" value="{{ $pair->taker_fee_pct }}">
                                <input type="hidden" name="is_active" value="{{ $pair->is_active ? 1 : 0 }}">
                                <button class="btn-outline text-xs">Update Price</button>
                            </form>
                            <form method="POST" action="{{ route('admin.markets.update', $pair) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="maker_fee_pct" value="{{ $pair->maker_fee_pct }}">
                                <input type="hidden" name="taker_fee_pct" value="{{ $pair->taker_fee_pct }}">
                                <input type="hidden" name="is_active" value="{{ $pair->is_active ? 0 : 1 }}">
                                <button class="text-xs text-brand hover:underline">{{ $pair->is_active ? 'Pause' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
