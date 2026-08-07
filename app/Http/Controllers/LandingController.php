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
                'subtitle' => 'Buy and sell crypto with a real order book, live market prices, market/limit orders and transparent fees.',
                'features' => [
                    'Market and limit order types, matched directly against other users\' orders',
                    'Live order book and recent trades',
                    'Funds lock automatically while an order is open',
                    'All fills post through the double-entry ledger',
                ],
                'risk' => 'Spot trading involves real risk of loss. Execution depends on order book depth — an order may fill only partially, or not at all, if no counterparty is available.',
                'cta' => ['Open Spot Trading', '/app/spot'],
            ],
            'stocks' => [
                'title' => 'Stock Trading (Paper Mode)',
                'tag' => 'Trade',
                'subtitle' => 'Paper-trade major listed equities like AAPL, MSFT, TSLA, NVDA and AMZN.',
                'features' => [
                    'Watchlists and price tracking',
                    'Buy/sell order tickets with instant paper fills',
                    'Portfolio holdings and P&L tracking',
                    'No real brokerage connection until a licensed broker adapter (e.g. Alpaca, Tradier, DriveWealth) and market data vendor are configured',
                ],
                'risk' => 'This is a paper-trading environment. No real securities are bought or sold, and prices are not yet fed by a licensed market data vendor.',
                'cta' => ['Open Stock Trading', '/app/stocks'],
            ],
            'forex' => [
                'title' => 'Forex Trading (Paper Mode)',
                'tag' => 'Trade',
                'subtitle' => 'Major and minor currency pairs with spreads and a pip calculator.',
                'features' => [
                    'EUR/USD, GBP/USD, USD/JPY and more',
                    'Bid/ask spreads',
                    'Leverage and margin requirement tracking',
                    'MetaTrader 5 account linking available',
                ],
                'risk' => 'Forex trading carries a high level of risk and may not be suitable for all investors. This runs in paper mode until a licensed forex data feed and broker are connected.',
                'cta' => ['Open Forex Trading', '/app/forex'],
            ],
            'futures' => [
                'title' => 'Futures Trading',
                'tag' => 'Trade',
                'subtitle' => 'Leveraged perpetual futures with cross/isolated margin, funding rate and liquidation price — high risk.',
                'features' => [
                    'Configurable leverage on select markets, settled against real crypto price movement',
                    'Cross and isolated margin modes',
                    'Real-time mark price, index price and funding rate',
                    'Requires KYC and a separate futures risk agreement',
                ],
                'risk' => 'Futures trading is extremely high risk and can result in losses exceeding your deposit. This runs on Bitzlatoview\'s internal engine, not a live external exchange, until broker/clearing licensing is in place.',
                'cta' => ['Open Futures Trading', '/app/futures'],
            ],
            'nft' => [
                'title' => 'NFT Marketplace',
                'tag' => 'Web3',
                'subtitle' => 'Browse collections, connect a Web3 wallet, and explore floor price, volume and rarity data.',
                'features' => [
                    'Collection and item detail pages',
                    'WalletConnect-based wallet linking',
                    'Listing/bid flows settled internally through the ledger',
                    'No real on-chain trading until smart contracts/providers are connected',
                ],
                'risk' => 'NFTs are speculative and illiquid assets. Listings on this platform do not yet settle on-chain.',
                'cta' => ['Browse NFTs', '/app/nft'],
            ],
            'swap' => [
                'title' => 'Crypto Swap',
                'tag' => 'Trade',
                'subtitle' => 'Instantly convert between assets using live market rates, slippage tolerance and a transparent fee breakdown.',
                'features' => [
                    'Live rate with configurable slippage tolerance',
                    'Minimum received and price impact estimate',
                    'Swap between any two wallets you own',
                    'Future DEX aggregator / CEX liquidity provider adapters',
                ],
                'risk' => 'Swap rates track live market prices but fills settle internally on Bitzlatoview\'s own ledger rather than on-chain or against external exchange liquidity.',
                'cta' => ['Open Swap', '/app/swap'],
            ],
            'buy-crypto' => [
                'title' => 'Buy / Sell Crypto',
                'tag' => 'Buy Crypto',
                'subtitle' => 'A beginner-friendly way to buy crypto with your wallet balance, or sell back — quote preview and fee shown up front.',
                'features' => [
                    'Buy instantly using your Bitzlatoview balance',
                    'Sell crypto back to your balance instantly',
                    'Or use P2P for local payment methods',
                    'Fee preview before you confirm',
                ],
                'risk' => 'Prices track live market rates. Fills settle internally on Bitzlatoview\'s ledger. Fund your balance first via Deposit before buying.',
                'cta' => ['Buy or Sell Now', '/app/buy-sell'],
            ],
            'p2p' => [
                'title' => 'P2P Trading Marketplace',
                'tag' => 'Buy Crypto',
                'subtitle' => 'Trade directly with other verified users using local payment methods, protected by real on-platform escrow.',
                'features' => [
                    'Escrow locks the seller\'s crypto — held in the ledger until both sides confirm',
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
                'risk' => 'Copy trading can amplify gains and losses. Past performance does not guarantee future results. No returns are guaranteed, and you can lose your allocated amount.',
                'cta' => ['Browse Traders', '/app/copy-trading'],
            ],
            'ai-trading-bot' => [
                'title' => 'AI Trading Bot Investment',
                'tag' => 'Earn & Automate',
                'subtitle' => 'Allocate to grid, DCA, trend-following or aggressive strategies running on Bitzlatoview\'s internal engine.',
                'features' => [
                    'Conservative, balanced, aggressive, grid, DCA and trend strategies',
                    'Disclosed historical performance and max drawdown shown per bot',
                    'Start, pause or stop a bot allocation at any time',
                    'All gains/losses post through the ledger and track real market prices',
                ],
                'risk' => 'AI trading bots are experimental and may lose money. Bitzlatoview never guarantees returns. Bots run on Bitzlatoview\'s own engine, not a live connection to an external exchange.',
                'cta' => ['View Bot Marketplace', '/app/ai-bots'],
            ],
            'mining' => [
                'title' => 'Crypto Mining Contracts',
                'tag' => 'Earn & Automate',
                'subtitle' => 'Transparent hashpower contracts with clear hashrate, term, maintenance fee and reward formula.',
                'features' => [
                    'BTC, ETH and LTC hashpower packages',
                    'Clear term length and maintenance fee disclosure',
                    'Rewards credit automatically to your chosen wallet',
                    'Full contract history and reward log',
                ],
                'risk' => 'Mining rewards follow the disclosed reward rate and are not guaranteed. Real mining profitability depends on network difficulty and coin price, and can go to zero.',
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
                'risk' => 'Investment products carry risk of loss. Rates are illustrative, not guaranteed.',
                'cta' => ['View Investment Products', '/app/investments'],
            ],
            'metatrader-5' => [
                'title' => 'MetaTrader 5 / Meta Trading',
                'tag' => 'Trade',
                'subtitle' => 'Connect a broker/MT5 account, sync positions and trade history, and explore an umbrella of MT5, forex, copy-signal and EA tools.',
                'features' => [
                    'MT5 account linking (login, server, leverage, currency)',
                    'Sync positions and trade history',
                    'Web terminal, EA marketplace and VPS — coming as broker integrations are finalized',
                    'Credentials are never stored in plain text',
                ],
                'risk' => 'MT5 officially supports forex, stocks, futures, algorithmic and copy trading. A live broker connection requires proper licensing and is not yet active — accounts linked here do not yet sync with a real MT5 server.',
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
