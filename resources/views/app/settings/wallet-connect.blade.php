@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">WalletConnect</h1>
    <div class="risk-banner">External wallet balances are separate from your Bitzlatoview custodial ledger balance. Connecting a wallet does not credit any custodial balance.</div>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Connect a Wallet</h2>
        <div class="mb-4 grid grid-cols-3 gap-2 sm:grid-cols-6">
            @foreach (['metamask' => 'MetaMask', 'trust' => 'Trust Wallet', 'coinbase' => 'Coinbase', 'rainbow' => 'Rainbow', 'ledger' => 'Ledger', 'walletconnect' => 'WalletConnect QR'] as $key => $label)
                <button type="button" onclick="document.getElementById('provider').value='{{ $key }}'" class="btn-outline p-3 text-center text-xs">{{ $label }}</button>
            @endforeach
        </div>
        <form method="POST" action="{{ route('app.settings.wallet-connect.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" id="provider" name="provider" value="walletconnect">
            <div><label class="label-field">Wallet address</label><input type="text" name="address" class="input-field" placeholder="0x..." required></div>
            <div><label class="label-field">Chain</label><input type="text" name="chain" class="input-field" value="ethereum"></div>
            <div><label class="label-field">Label (optional)</label><input type="text" name="label" class="input-field"></div>
            <button class="btn-brand w-full">Connect Wallet</button>
        </form>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Connected Wallets</h2>
        <table class="data-table">
            <thead><tr><th>Provider</th><th>Address</th><th>Chain</th><th>Connected</th><th></th></tr></thead>
            <tbody>
                @forelse ($wallets as $w)
                    <tr>
                        <td>{{ ucfirst($w->provider) }}</td>
                        <td class="font-numeric text-xs">{{ \Illuminate\Support\Str::limit($w->address, 20) }}</td>
                        <td>{{ ucfirst($w->chain) }}</td>
                        <td class="text-text-muted">{{ $w->connected_at->diffForHumans() }}</td>
                        <td><form method="POST" action="{{ route('app.settings.wallet-connect.destroy', $w->id) }}">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Disconnect</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No wallets connected.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
