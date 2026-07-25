@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">NFT Collections</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Floor</th><th>Volume</th><th>Owners</th><th>Items</th></tr></thead>
            <tbody>
                @foreach ($collections as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td class="font-numeric">{{ $c->floor_price }} ETH</td>
                        <td class="font-numeric">{{ number_format($c->volume) }}</td>
                        <td class="font-numeric">{{ number_format($c->owners_count) }}</td>
                        <td class="font-numeric">{{ $c->items_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
