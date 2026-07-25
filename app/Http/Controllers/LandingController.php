<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    protected function catalog(): array
    {
        return [
            'crypto' => [
                'title' => 'Spot Crypto Trading',
                'tag' => 'Trade',
                'subtitle' => 'Buy and sell crypto with a full order book, live-simulated pricing, market/limit orders and transparent fees.',
                'features' => [
                    'Market, limit, stop-limit and stop-market order types',
                    'Live order book, depth chart and recent trades placeholder',
                    'Funds lock automatically while an order is open',
                    'All fills post through the double-entry ledger',
                ],
                'risk' => 'Spot trading involves risk of loss. Prices are simulated in this build.',
                'cta' => ['Open Spot Trading', '/app/spot'],
            ],
            'stocks' => [
                'title' => 'Stock Trading (Paper Mode)',
                'tag' => 'Trade',
                'subtitle' => 'Paper-trade major listed equities like AAPL, MSFT, TSLA, NVDA and AMZN with simulated pricing.',
                'features' => [
                    'Watchlists and simulated price charts',
                    'Buy/sell order tickets with instant paper fills',
                    'Portfolio holdings and P&L tracking',
                    'No real brokerage connection until a licensed broker adapter (e.g. Alpaca, Tradier, DriveWealth) is configured',
                ],
                'risk' => 'This is a simulated paper-trading environment. No real securities are bought or sold.',
                'cta' => ['Open Stock Trading', '/app/stocks'],
            ],
            'forex' => [
                'title' => 'Forex Trading (Paper Mode)',
                'tag' => 'Trade',
                'subtitle' => 'Simulated major and minor currency pairs with spreads, leverage placeholders and a pip calculator.',
                'features' => [
                    'EUR/USD, GBP/USD, USD/JPY and more',
                    'Simulated bid/ask spreads',
                    'Leverage and margin requirement placeholders',
                    'MetaTrader 5 account linking available',
                ],
                'risk' => 'Forex trading carries a high level of risk and may not be suitable for all investors. Simulated pricing only.',
                'cta' => ['Open Forex Trading', '/app/forex'],
            ],
            'futures' => [
                'title' => 'Futures Trading',
                'tag' => 'Trade',
                'subtitle' => 'Leveraged perpetual futures with cross/isolated margin, funding rate and liquidation price — high risk, simulation mode only.',
                'features' => [
                    'Up to 50x simulated leverage on select markets',
                    'Cross and isolated margin modes',
                    'Real-time (simulated) mark price, index price and funding rate',
                    'Requires KYC and a separate futures risk agreement',
                ],
                'risk' => 'Futures trading is extremely high risk and can result in losses exceeding your deposit. Simulated / paper mode only.',
                'cta' => ['Open Futures Trading', '/app/futures'],
            ],
            'nft' => [
                'title' => 'NFT Marketplace',
                'tag' => 'Web3',
                'subtitle' => 'Browse mock collections, connect a Web3 wallet, and explore floor price, volume and rarity data.',
                'features' => [
                    'Collection and item detail pages',
                    'WalletConnect-based wallet linking',
                    'Mock listing/bid flows for demonstration',
                    'No real on-chain trading until contracts/providers are connected',
                ],
                'risk' => 'NFTs are speculative and illiquid assets. This section uses mock data only.',
                'cta' => ['Browse NFTs', '/app/nft'],
            ],
            'swap' => [
                'title' => 'Crypto Swap',
                'tag' => 'Trade',
                'subtitle' => 'Instantly convert between assets with a transparent simulated rate, slippage tolerance and fee breakdown.',
                'features' => [
                    'Simulated rate with configurable slippage tolerance',
                    'Minimum received and price impact estimate',
                    'Swap between any two wallets you own',
                    'Future DEX aggregator / CEX liquidity provider adapters',
                ],
                'risk' => 'Swap rates are simulated for this build and may not reflect real market conditions.',
                'cta' => ['Open Swap', '/app/swap'],
            ],
            'buy-crypto' => [
                'title' => 'Buy / Sell Crypto',
                'tag' => 'Buy Crypto',
                'subtitle' => 'A beginner-friendly way to buy crypto with a fiat balance, or sell back to fiat — quote preview and fee shown up front.',
                'features' => [
                    'Buy with simulated card/bank balance',
                    'Sell crypto back to your fiat balance instantly',
                    'Or use P2P for local payment methods',
                    'Fee preview before you confirm',
                ],
                'risk' => 'Prices and fills are simulated. No real card/bank rails are connected in this build.',
                'cta' => ['Buy or Sell Now', '/app/buy-sell'],
            ],
            'p2p' => [
                'title' => 'P2P Trading Marketplace',
                'tag' => 'Buy Crypto',
                'subtitle' => 'Trade directly with other verified users using local payment methods, protected by on-platform escrow.',
                'features' => [
                    'Escrow locks the seller\'s crypto until both sides confirm',
                    'Verified merchant badges, completion rate and average release time',
                    'In-app chat with attachment upload',
                    'Admin-reviewed appeal system for disputes',
                ],
                'risk' => 'Only trade inside Bitzlatoview\'s escrow flow. Never send payment outside the platform or to unverified counterparties.',
                'cta' => ['Explore P2P', '/app/p2p'],
            ],
            'copy-trading' => [
                'title' => 'Copy Trading',
                'tag' => 'Earn & Automate',
                'subtitle' => 'Follow verified crypto, forex, futures and stock traders. Every trader profile shows risk score, drawdown and return history.',
                'features' => [
                    'Crypto, forex, futures and stock trader categories',
                    'Set stop-loss, take-profit, max position size and copy ratio',
                    'Pause or stop copying at any time',
                    'Allocations are funded from your Investment Wallet',
                ],
                'risk' => 'Copy trading can amplify gains and losses. Past performance does not guarantee future results. No returns are guaranteed.',
                'cta' => ['Browse Traders', '/app/copy-trading'],
            ],
            'ai-trading-bot' => [
                'title' => 'AI Trading Bot Investment',
                'tag' => 'Earn & Automate',
                'subtitle' => 'Allocate to grid, DCA, trend-following or aggressive strategies. Bots run in simulated/paper mode by default.',
                'features' => [
                    'Conservative, balanced, aggressive, grid, DCA and trend strategies',
                    'Historical simulated performance and max drawdown shown per bot',
                    'Start, pause or stop a bot allocation at any time',
                    'All gains/losses post through the ledger',
                ],
                'risk' => 'AI trading bots are experimental and may lose money. Bitzlatoview never guarantees returns.',
                'cta' => ['View Bot Marketplace', '/app/ai-bots'],
            ],
            'mining' => [
                'title' => 'Crypto Mining Contracts',
                'tag' => 'Earn & Automate',
                'subtitle' => 'Transparent simulated hashpower contracts with clear hashrate, term, maintenance fee and reward formula.',
                'features' => [
                    'BTC, ETH and LTC hashpower packages',
                    'Clear term length and maintenance fee disclosure',
                    'Rewards credit automatically to your chosen wallet',
                    'Full contract history and reward log',
                ],
                'risk' => 'Mining rewards are simulated and not guaranteed. Real mining profitability depends on network difficulty and coin price, and can go to zero.',
                'cta' => ['View Mining Packages', '/app/mining'],
            ],
            'investments' => [
                'title' => 'Investment / Earn Products',
                'tag' => 'Earn & Automate',
                'subtitle' => 'Flexible and fixed-term yield products across USDT, BTC and ETH, with transparent APY and lock periods.',
                'features' => [
                    'Flexible (no lock) and fixed-term products',
                    'Transparent illustrative APY, not guaranteed',
                    'Rewards accrue and post to your Investment Wallet',
                    'Full subscription and redemption history',
                ],
                'risk' => 'Investment products carry risk of loss. Rates are illustrative and simulated, not guaranteed.',
                'cta' => ['View Investment Products', '/app/investments'],
            ],
            'metatrader-5' => [
                'title' => 'MetaTrader 5 / Meta Trading',
                'tag' => 'Trade',
                'subtitle' => 'Connect a broker/MT5 account, sync positions and trade history, and explore an umbrella of MT5, forex, copy-signal and EA tools.',
                'features' => [
                    'Simulated MT5 account linking (login, server, leverage, currency)',
                    'Sync positions and trade history placeholders',
                    'Web terminal, EA marketplace and VPS placeholders',
                    'Credentials are never stored in plain text',
                ],
                'risk' => 'MT5 official support covers forex, stocks, futures, algorithmic and copy trading. Real broker connections require proper licensing.',
                'cta' => ['Connect MT5', '/app/metatrader-5'],
            ],
        ];
    }

    public function show(Request $request, string $slug)
    {
        $catalog = $this->catalog();

        abort_unless(isset($catalog[$slug]), 404);

        return view('public.landing', ['page' => $catalog[$slug], 'slug' => $slug]);
    }
}
