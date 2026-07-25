@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Virtual Cards</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Card</th><th>Limit</th><th>Status</th><th>Transactions</th><th></th></tr></thead>
            <tbody>
                @forelse ($cards as $card)
                    <tr>
                        <td>{{ $card->user->email }}</td>
                        <td class="font-numeric">{{ $card->masked_number }}</td>
                        <td class="font-numeric">${{ number_format($card->spending_limit, 2) }} {{ $card->currency }}</td>
                        <td><span class="pill-{{ $card->status === 'active' ? 'success' : ($card->status === 'frozen' ? 'warning' : 'muted') }}">{{ $card->status }}</span></td>
                        <td class="font-numeric">{{ $card->transactions->count() }}</td>
                        <td>
                            @if ($card->status === 'active')
                                <form method="POST" action="{{ route('admin.virtual-cards.freeze', $card) }}">@csrf<button class="text-xs text-danger hover:underline">Freeze</button></form>
                            @elseif ($card->status === 'frozen')
                                <form method="POST" action="{{ route('admin.virtual-cards.unfreeze', $card) }}">@csrf<button class="text-xs text-brand hover:underline">Unfreeze</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No virtual cards issued yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $cards->links() }}
</div>
@endsection
