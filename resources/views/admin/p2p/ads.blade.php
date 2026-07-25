@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">P2P Ads</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Side</th><th>Asset</th><th>Price</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($ads as $ad)
                    <tr>
                        <td>{{ $ad->user->email }}</td>
                        <td>{{ ucfirst($ad->side) }}</td>
                        <td>{{ $ad->asset->symbol }}</td>
                        <td class="font-numeric">{{ number_format($ad->price, 4) }} {{ $ad->fiat_currency }}</td>
                        <td><span class="pill-{{ $ad->status === 'active' ? 'success' : 'muted' }}">{{ $ad->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No ads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $ads->links() }}
</div>
@endsection
