@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6" x-data="walletConnectPanel()">
    <h1 class="text-2xl font-bold">WalletConnect</h1>
    <div class="risk-banner">External wallet balances are separate from your Bitzlatoview custodial ledger balance. Connecting a wallet only records its public address for reference — it never grants Bitzlatoview access to your funds or requires a signature.</div>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Connect a Wallet</h2>
        <div class="mb-4 grid grid-cols-3 gap-3 sm:grid-cols-6">
            <template x-for="w in wallets" :key="w.key">
                <button type="button" @click="connect(w)" class="flex flex-col items-center gap-2 rounded-lg border border-border bg-surface-2 p-3 text-center text-xs transition hover:border-brand" :class="{ 'opacity-60': w.needsConfig }">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold text-background" :style="{ background: w.color }" x-text="w.initial"></span>
                    <span x-text="w.label"></span>
                </button>
            </template>
        </div>

        <p x-show="status" x-cloak class="mb-3 text-xs" :class="statusIsError ? 'text-danger' : 'text-brand'" x-text="status"></p>

        <form method="POST" action="{{ route('app.settings.wallet-connect.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="provider" x-model="provider">
            <div><label class="label-field">Wallet address</label><input type="text" name="address" x-model="address" class="input-field" placeholder="0x..." required></div>
            <div><label class="label-field">Chain</label><input type="text" name="chain" x-model="chain" class="input-field" value="ethereum"></div>
            <div><label class="label-field">Label (optional)</label><input type="text" name="label" class="input-field"></div>
            <button class="btn-brand w-full">Save Connection</button>
        </form>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Connected Wallets</h2>
        <div class="overflow-x-auto">
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
</div>

<script>
    function walletConnectPanel() {
        return {
            provider: '',
            address: '',
            chain: 'ethereum',
            status: '',
            statusIsError: false,
            wallets: [
                { key: 'metamask', label: 'MetaMask', initial: 'M', color: '#F6851B', flag: 'isMetaMask' },
                { key: 'coinbase', label: 'Coinbase Wallet', initial: 'C', color: '#0052FF', flag: 'isCoinbaseWallet' },
                { key: 'trust', label: 'Trust Wallet', initial: 'T', color: '#3375BB', flag: 'isTrust' },
                { key: 'rainbow', label: 'Rainbow', initial: 'R', color: '#001E59', flag: 'isRainbow' },
                { key: 'ledger', label: 'Ledger Live', initial: 'L', color: '#2E2E2E', flag: null, needsConfig: true },
                { key: 'walletconnect', label: 'WalletConnect QR', initial: 'W', color: '#3B99FC', flag: null, needsConfig: true },
            ],
            findProvider(flag) {
                if (typeof window.ethereum === 'undefined') return null;
                const candidates = window.ethereum.providers && window.ethereum.providers.length
                    ? window.ethereum.providers
                    : [window.ethereum];
                if (!flag) return candidates[0];
                return candidates.find(p => p[flag]) || null;
            },
            async connect(w) {
                this.status = '';
                this.provider = w.key;

                if (w.needsConfig) {
                    this.statusIsError = true;
                    this.status = w.label + ' requires a WalletConnect Cloud project ID to be configured on the server before QR/hardware pairing can go live. Use a browser-extension wallet below, or paste your address manually.';
                    return;
                }

                const eth = this.findProvider(w.flag);
                if (!eth) {
                    this.statusIsError = true;
                    this.status = w.label + ' browser extension was not detected. Install it, or paste your address manually below.';
                    return;
                }

                try {
                    const accounts = await eth.request({ method: 'eth_requestAccounts' });
                    const chainId = await eth.request({ method: 'eth_chainId' });
                    this.address = accounts[0];
                    this.chain = this.chainName(chainId);
                    this.statusIsError = false;
                    this.status = 'Connected to ' + w.label + '. Review and save below.';
                } catch (err) {
                    this.statusIsError = true;
                    this.status = 'Connection request was rejected or failed.';
                }
            },
            chainName(hexId) {
                const map = { '0x1': 'ethereum', '0x38': 'bsc', '0x89': 'polygon', '0xa4b1': 'arbitrum', '0xa': 'optimism' };
                return map[hexId] || hexId;
            },
        };
    }
</script>
@endsection
