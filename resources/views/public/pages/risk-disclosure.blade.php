@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">Risk Disclosure</h1>
    <p class="mt-2 text-sm text-text-muted">Last updated: {{ now()->format('F j, Y') }}</p>

    <div class="prose prose-invert mt-8 max-w-none space-y-5 text-text-muted">
        <p>Trading and investing in the products offered on Bitzlatoview carries significant risk. Please read this disclosure in full before using the Platform.</p>

        <h2 class="text-text-main">General Risk</h2>
        <p>The prices of cryptocurrencies, stocks, forex, futures and NFTs are highly volatile and can move sharply in either direction. You may lose some or all of the funds you allocate to any product on the Platform. Bitzlatoview does not provide investment advice, and nothing on the Platform should be construed as a recommendation to buy or sell any asset.</p>

        <h2 class="text-text-main">Futures &amp; Leverage</h2>
        <p>Futures trading involves leverage and can result in losses that exceed your initial margin. Liquidation can occur rapidly during volatile markets. Futures access requires a separate risk acknowledgment and KYC verification.</p>

        <h2 class="text-text-main">Forex</h2>
        <p>Forex trading is speculative, carries a high level of risk, and may not be suitable for all investors. Leverage can magnify both gains and losses.</p>

        <h2 class="text-text-main">Copy Trading</h2>
        <p>When you copy a trader, your account replicates trades based on their strategy. Past performance of any trader is not indicative of future results, and copy trading can result in losses even if the copied trader has a positive track record.</p>

        <h2 class="text-text-main">AI Trading Bots</h2>
        <p>AI trading bots are experimental automated strategies running on Bitzlatoview's internal engine. They are not guaranteed to be profitable and may lose money, including during conditions that looked favorable in past performance data but do not repeat going forward.</p>

        <h2 class="text-text-main">Mining Contracts</h2>
        <p>Mining rewards depend on network difficulty, coin price, and maintenance fees, all of which can change unpredictably. Mining contract returns are never guaranteed and can go to zero.</p>

        <h2 class="text-text-main">Staking / Earn / Investment Products</h2>
        <p>Yield figures shown for Earn/investment products are illustrative and not guaranteed. Locked products restrict access to your funds for the stated term.</p>

        <h2 class="text-text-main">P2P Trading</h2>
        <p>While P2P trades are escrow-protected on Bitzlatoview, off-platform payment risk (e.g. chargebacks, payment fraud) exists if you deviate from platform guidance. Never trade outside the Bitzlatoview escrow flow.</p>

        <h2 class="text-text-main">Stocks &amp; NFTs</h2>
        <p>Equities and NFTs can be illiquid and volatile. NFT valuations shown on the Platform are indicative and may not reflect achievable sale prices.</p>

        <p class="font-semibold text-text-main">By using Bitzlatoview, you acknowledge that you understand and accept these risks.</p>
    </div>
</div>
@endsection
