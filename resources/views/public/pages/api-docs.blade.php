@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">API Documentation</h1>
    <p class="mt-2 text-text-muted">Bitzlatoview exposes a REST API mirroring the actions available in the dashboard. Below is a summary of key endpoint groups (see the codebase <code>routes/</code> directory for the full list).</p>

    <div class="mt-8 space-y-6">
        @foreach ([
            'Markets' => ['GET /api/markets', 'GET /api/markets/{symbol}', 'GET /api/top-gainers', 'GET /api/top-losers'],
            'Wallets' => ['GET /api/wallets', 'POST /api/wallets/deposit', 'POST /api/wallets/withdraw', 'POST /api/wallets/transfer'],
            'Trading' => ['POST /api/orders', 'GET /api/orders', 'DELETE /api/orders/{id}', 'GET /api/trades'],
            'Swap' => ['GET /api/swap/quote', 'POST /api/swap/execute'],
            'P2P' => ['GET /api/p2p/ads', 'POST /api/p2p/orders', 'POST /api/p2p/orders/{id}/release'],
        ] as $group => $endpoints)
            <div class="glass-card p-5">
                <h3 class="font-semibold text-brand">{{ $group }}</h3>
                <ul class="mt-3 space-y-1 font-numeric text-sm text-text-muted">
                    @foreach ($endpoints as $endpoint)
                        <li>{{ $endpoint }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <p class="mt-8 text-sm text-text-muted">All financial endpoints require an authenticated Bearer token and support idempotency keys via the <code>Idempotency-Key</code> header. Rate limits apply.</p>
</div>
@endsection
