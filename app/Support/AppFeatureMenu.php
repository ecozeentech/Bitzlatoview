<?php

namespace App\Support;

/**
 * Single source of truth for "every feature available in the user app" — used by both the
 * sidebar (partials.app-sidebar) and the dashboard's "All Features" popup
 * (components.features-popup) so the two can never drift out of sync. Add a new user-facing
 * /app/* feature here once, and it appears in both places automatically.
 *
 * Each entry is [label, route name (informational only), url prefix].
 */
class AppFeatureMenu
{
    /**
     * Each entry: [English label (also the icon lookup key — see x-nav-icon), route name,
     * url prefix, translation key (lang/*\/common.php)]. The English label is kept stable for
     * icon lookups regardless of active locale; use translatedLabel() to get display text.
     */
    public static function groups(): array
    {
        return [
            null => [
                ['Dashboard', 'app.dashboard', 'app/dashboard', 'dashboard'],
            ],
            'Trade' => [
                ['Markets', 'app.markets', 'app/markets', 'markets'],
                ['Spot Trading', 'app.spot', 'app/spot', 'spot_trading'],
                ['Buy / Sell', 'app.buy-sell', 'app/buy-sell', 'buy_sell'],
                ['Swap', 'app.swap', 'app/swap', 'swap'],
                ['Futures', 'app.futures', 'app/futures', 'futures'],
                ['Stocks', 'app.stocks', 'app/stocks', 'stocks'],
                ['Forex', 'app.forex', 'app/forex', 'forex'],
                ['MetaTrader 5', 'app.mt5', 'app/metatrader-5', 'metatrader_5'],
            ],
            'Earn & Automate' => [
                ['P2P', 'app.p2p', 'app/p2p', 'p2p'],
                ['Copy Trading', 'app.copy-trading', 'app/copy-trading', 'copy_trading'],
                ['Signals', 'app.signals', 'app/signals', 'signals'],
                ['AI Bots', 'app.ai-bots', 'app/ai-bots', 'ai_bots'],
                ['Mining', 'app.mining', 'app/mining', 'mining'],
                ['Investments', 'app.investments', 'app/investments', 'investments'],
                ['NFT', 'app.nft', 'app/nft', 'nft'],
            ],
            'Wallets' => [
                ['Primary Wallet', 'app.wallet.primary', 'app/wallet/primary', 'primary_wallet'],
                ['Trading Wallet', 'app.wallet.trading', 'app/wallet/trading', 'trading_wallet'],
                ['Investment Wallet', 'app.wallet.investment', 'app/wallet/investment', 'investment_wallet'],
                ['Deposit', 'app.funding.deposit', 'app/funding/deposit', 'deposit'],
                ['Withdraw', 'app.funding.withdraw', 'app/funding/withdraw', 'withdraw'],
                ['Transaction History', 'app.funding.transactions', 'app/funding/transactions', 'transaction_history'],
            ],
            'Account' => [
                ['Virtual Cards', 'app.virtual-cards', 'app/virtual-cards', 'virtual_cards'],
                ['Tax Center', 'app.tax', 'app/tax', 'tax_center'],
                ['Analyst Packages', 'app.analyst-packages', 'app/analyst-packages', 'analyst_packages'],
                ['News', 'app.news', 'app/news', 'news'],
                ['Blog', 'app.blog', 'app/blog', 'blog'],
                ['Referrals', 'app.referrals', 'app/referrals', 'referrals'],
                ['Support', 'app.support', 'app/support', 'support'],
                ['Settings', 'app.settings.profile', 'app/settings', 'settings'],
            ],
        ];
    }

    /** Flat list of every [label, routeName, prefix, translationKey] entry across all groups. */
    public static function flat(): array
    {
        return array_merge(...array_values(self::groups()));
    }

    public static function translatedLabel(string $englishLabel, string $translationKey): string
    {
        return __('common.'.$translationKey) !== 'common.'.$translationKey
            ? __('common.'.$translationKey)
            : $englishLabel;
    }
}
