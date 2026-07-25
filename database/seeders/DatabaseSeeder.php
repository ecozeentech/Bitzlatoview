<?php

namespace Database\Seeders;

use App\Models\AiBot;
use App\Models\Asset;
use App\Models\Balance;
use App\Models\BillingPackage;
use App\Models\BlogPost;
use App\Models\Cardholder;
use App\Models\CmsPage;
use App\Models\CopyTraderProfile;
use App\Models\EmailTemplate;
use App\Models\FaqItem;
use App\Models\FeatureFlag;
use App\Models\FeeSchedule;
use App\Models\ForexPair;
use App\Models\FuturesMarket;
use App\Models\InvestmentProduct;
use App\Models\MarketPair;
use App\Models\MiningPackage;
use App\Models\Network;
use App\Models\NewsArticle;
use App\Models\NftCollection;
use App\Models\NftItem;
use App\Models\P2PAd;
use App\Models\P2PMerchantProfile;
use App\Models\P2PPaymentMethod;
use App\Models\StockInstrument;
use App\Models\SystemSetting;
use App\Models\TaxReport;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\VirtualCard;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\WalletProvisioningService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'Bitzlatoview Admin',
            'full_legal_name' => 'Bitzlatoview Admin',
            'email' => 'admin@bitzlatoview.com',
            'phone' => '+10000000001',
            'country' => 'US',
            'city' => 'New York',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'kyc_status' => 'approved',
            'referral_code' => 'ADMIN001',
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
            'risk_accepted_at' => now(),
            'two_factor_enabled' => true,
        ]);

        $verified = User::query()->create([
            'name' => 'Demo Verified',
            'full_legal_name' => 'Demo Verified User',
            'email' => 'demo@bitzlatoview.com',
            'phone' => '+10000000002',
            'country' => 'US',
            'city' => 'Austin',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
            'kyc_status' => 'approved',
            'referral_code' => 'DEMOVER1',
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
            'risk_accepted_at' => now(),
            'futures_agreement_at' => now(),
        ]);

        $unverified = User::query()->create([
            'name' => 'Demo Unverified',
            'full_legal_name' => 'Demo Unverified User',
            'email' => 'unverified@bitzlatoview.com',
            'phone' => '+10000000003',
            'country' => 'NG',
            'city' => 'Lagos',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
            'kyc_status' => 'not_started',
            'referral_code' => 'DEMOUNV1',
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
            'risk_accepted_at' => now(),
        ]);

        $seller = User::query()->create([
            'name' => 'P2P Merchant',
            'full_legal_name' => 'P2P Merchant Demo',
            'email' => 'merchant@bitzlatoview.com',
            'phone' => '+10000000004',
            'country' => 'GB',
            'city' => 'London',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
            'kyc_status' => 'approved',
            'referral_code' => 'MERCHANT1',
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
            'risk_accepted_at' => now(),
        ]);

        $walletService = app(WalletProvisioningService::class);
        foreach ([$admin, $verified, $unverified, $seller] as $user) {
            UserProfile::query()->create(['user_id' => $user->id, 'occupation' => 'Trader']);
            $walletService->provision($user);
        }

        $assets = collect([
            ['symbol' => 'BTC', 'name' => 'Bitcoin', 'type' => 'crypto', 'mock_price_usd' => 67500, 'change_24h' => 2.4],
            ['symbol' => 'ETH', 'name' => 'Ethereum', 'type' => 'crypto', 'mock_price_usd' => 3450, 'change_24h' => 1.1],
            ['symbol' => 'USDT', 'name' => 'Tether', 'type' => 'crypto', 'mock_price_usd' => 1, 'change_24h' => 0.01],
            ['symbol' => 'USDC', 'name' => 'USD Coin', 'type' => 'crypto', 'mock_price_usd' => 1, 'change_24h' => 0.0],
            ['symbol' => 'SOL', 'name' => 'Solana', 'type' => 'crypto', 'mock_price_usd' => 148, 'change_24h' => 4.8],
            ['symbol' => 'XRP', 'name' => 'XRP', 'type' => 'crypto', 'mock_price_usd' => 0.62, 'change_24h' => -1.2],
            ['symbol' => 'DOGE', 'name' => 'Dogecoin', 'type' => 'crypto', 'mock_price_usd' => 0.14, 'change_24h' => 6.5],
            ['symbol' => 'LTC', 'name' => 'Litecoin', 'type' => 'crypto', 'mock_price_usd' => 84, 'change_24h' => -0.8],
            ['symbol' => 'USD', 'name' => 'US Dollar', 'type' => 'fiat', 'mock_price_usd' => 1, 'change_24h' => 0],
        ])->mapWithKeys(function ($data) {
            $asset = Asset::query()->create($data);

            return [$data['symbol'] => $asset];
        });

        foreach ([
            ['name' => 'Bitcoin', 'code' => 'BTC', 'asset_id' => $assets['BTC']->id, 'confirmations' => 2],
            ['name' => 'Ethereum', 'code' => 'ETH', 'asset_id' => $assets['ETH']->id, 'confirmations' => 12],
            ['name' => 'BSC', 'code' => 'BSC', 'asset_id' => $assets['USDT']->id, 'confirmations' => 15],
            ['name' => 'Polygon', 'code' => 'POLYGON', 'asset_id' => $assets['USDC']->id, 'confirmations' => 30],
            ['name' => 'Solana', 'code' => 'SOL', 'asset_id' => $assets['SOL']->id, 'confirmations' => 32],
            ['name' => 'Tron', 'code' => 'TRX', 'asset_id' => $assets['USDT']->id, 'confirmations' => 20],
        ] as $network) {
            Network::query()->create([...$network, 'min_deposit' => 0.001, 'withdrawal_fee' => 1, 'is_active' => true]);
        }

        $pairs = [
            ['BTC', 'USDT', 67500, 2.4, 120000000],
            ['ETH', 'USDT', 3450, 1.1, 80000000],
            ['SOL', 'USDT', 148, 4.8, 35000000],
            ['XRP', 'USDT', 0.62, -1.2, 22000000],
            ['DOGE', 'USDT', 0.14, 6.5, 18000000],
            ['LTC', 'USDT', 84, -0.8, 9000000],
        ];
        foreach ($pairs as [$base, $quote, $price, $change, $volume]) {
            MarketPair::query()->create([
                'symbol' => $base.'-'.$quote,
                'base_asset_id' => $assets[$base]->id,
                'quote_asset_id' => $assets[$quote]->id,
                'last_price' => $price,
                'change_24h' => $change,
                'high_24h' => $price * 1.03,
                'low_24h' => $price * 0.97,
                'volume_24h' => $volume,
                'is_active' => true,
            ]);
        }

        foreach ([
            ['AAPL', 'Apple Inc.', 214.50, 1.2],
            ['MSFT', 'Microsoft', 430.10, 0.8],
            ['TSLA', 'Tesla', 248.30, 3.4],
            ['NVDA', 'NVIDIA', 118.70, 2.1],
            ['AMZN', 'Amazon', 196.40, -0.5],
        ] as [$symbol, $name, $price, $change]) {
            StockInstrument::query()->create([
                'symbol' => $symbol,
                'name' => $name,
                'last_price' => $price,
                'change_24h' => $change,
                'paper_only' => true,
            ]);
        }

        foreach ([
            ['EUR/USD', 'EUR', 'USD', 1.08410, 1.08430],
            ['GBP/USD', 'GBP', 'USD', 1.27320, 1.27350],
            ['USD/JPY', 'USD', 'JPY', 156.120, 156.150],
        ] as [$symbol, $base, $quote, $bid, $ask]) {
            ForexPair::query()->create([
                'symbol' => $symbol,
                'base_currency' => $base,
                'quote_currency' => $quote,
                'bid' => $bid,
                'ask' => $ask,
                'spread' => $ask - $bid,
                'paper_only' => true,
            ]);
        }

        FuturesMarket::query()->create([
            'symbol' => 'BTC-PERP',
            'base_asset_id' => $assets['BTC']->id,
            'mark_price' => 67520,
            'index_price' => 67510,
            'funding_rate' => 0.0001,
            'max_leverage' => 20,
            'paper_only' => true,
        ]);
        FuturesMarket::query()->create([
            'symbol' => 'ETH-PERP',
            'base_asset_id' => $assets['ETH']->id,
            'mark_price' => 3455,
            'index_price' => 3452,
            'funding_rate' => 0.00008,
            'max_leverage' => 20,
            'paper_only' => true,
        ]);

        $ledger = app(LedgerService::class);
        foreach ([$verified, $seller, $admin] as $user) {
            $primary = $user->walletAccount('PRIMARY');
            $trading = $user->walletAccount('TRADING');
            $investment = $user->walletAccount('INVESTMENT');
            $ledger->creditAvailable($primary, $assets['USDT'], '50000', 'seed', 'seed-usdt-'.$user->id, null, null, 'Seed USDT');
            $ledger->creditAvailable($primary, $assets['BTC'], '1.5', 'seed', 'seed-btc-'.$user->id, null, null, 'Seed BTC');
            $ledger->creditAvailable($trading, $assets['USDT'], '25000', 'seed', 'seed-tusdt-'.$user->id, null, null, 'Seed trading USDT');
            $ledger->creditAvailable($trading, $assets['ETH'], '10', 'seed', 'seed-teth-'.$user->id, null, null, 'Seed trading ETH');
            $ledger->creditAvailable($investment, $assets['USDT'], '15000', 'seed', 'seed-iusdt-'.$user->id, null, null, 'Seed investment USDT');
        }

        foreach (['Bank Transfer', 'PayPal', 'Mobile Money', 'Wise', 'Revolut'] as $i => $name) {
            P2PPaymentMethod::query()->create(['name' => $name, 'code' => Str::slug($name), 'is_active' => true]);
        }

        $merchant = P2PMerchantProfile::query()->create([
            'user_id' => $seller->id,
            'is_verified' => true,
            'completed_trades' => 128,
            'completion_rate' => 98.5,
            'positive_feedback_rate' => 99.1,
            'avg_release_minutes' => 8,
            'is_online' => true,
            'terms' => 'Payment account name must match verified legal name.',
            'auto_reply' => 'Thanks for trading on Bitzlatoview P2P.',
        ]);

        foreach ([
            ['USDT', 'sell', 1.00, 5000, 50, 2000, 'USD'],
            ['BTC', 'sell', 67800, 0.5, 100, 5000, 'USD'],
            ['ETH', 'sell', 3460, 5, 50, 3000, 'EUR'],
            ['USDC', 'sell', 1.00, 8000, 20, 1500, 'GBP'],
        ] as [$sym, $side, $price, $avail, $min, $max, $fiat]) {
            P2PAd::query()->create([
                'user_id' => $seller->id,
                'merchant_profile_id' => $merchant->id,
                'asset_id' => $assets[$sym]->id,
                'side' => $side,
                'fiat_currency' => $fiat,
                'price_type' => 'fixed',
                'price' => $price,
                'available_amount' => $avail,
                'min_limit' => $min,
                'max_limit' => $max,
                'payment_method_ids' => [1, 2, 3],
                'terms' => 'Release after confirmed payment. No off-platform deals.',
                'is_visible' => true,
                'status' => 'active',
            ]);
        }

        foreach ([
            ['Nova Crypto', 'crypto', 12.4, 28.1, 8.2, 62, 1840],
            ['FX Pulse', 'forex', 6.1, 14.0, 11.5, 55, 920],
            ['Perp Apex', 'futures', 18.2, 41.0, 22.0, 48, 640],
            ['Equity Scout', 'stock', 4.8, 11.2, 7.0, 58, 410],
            ['P2P Relay', 'p2p', 3.2, 8.5, 4.0, 90, 1200],
        ] as [$name, $cat, $r30, $r90, $dd, $win, $followers]) {
            CopyTraderProfile::query()->create([
                'display_name' => $name,
                'category' => $cat,
                'bio' => 'Demo trader profile for Bitzlatoview copy trading simulation.',
                'strategy' => 'Rules-based discretionary simulation',
                'is_verified' => true,
                'is_featured' => true,
                'risk_level' => $dd > 15 ? 'high' : 'medium',
                'return_30d' => $r30,
                'return_90d' => $r90,
                'max_drawdown' => $dd,
                'win_rate' => $win,
                'followers' => $followers,
                'assets_traded' => ['BTC', 'ETH', 'USDT'],
            ]);
        }

        foreach ([
            ['Steady Grid', 'grid', 'conservative', 3.2, 4.5, 100],
            ['Trend Rider', 'trend', 'balanced', 8.4, 12.0, 250],
            ['Pulse Aggressive', 'aggressive', 'aggressive', 15.0, 25.0, 500],
            ['DCA Builder', 'dca', 'conservative', 5.1, 6.0, 100],
        ] as [$name, $type, $risk, $ret, $dd, $min]) {
            AiBot::query()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'strategy_type' => $type,
                'description' => 'Simulated AI strategy. Experimental and may lose money.',
                'risk_level' => $risk,
                'risk_score' => $dd / 2,
                'max_drawdown' => $dd,
                'simulated_return_30d' => $ret,
                'min_allocation' => $min,
                'supported_assets' => ['BTC', 'ETH', 'USDT'],
                'is_simulated' => true,
            ]);
        }

        foreach ([
            ['BTC Starter Rig', 'BTC', 100, 'TH/s', 30, 500, 0.00035],
            ['ETH Hash Pack', 'ETH', 250, 'MH/s', 60, 800, 0.004],
            ['SOL Compute', 'SOL', 50, 'GH/s', 90, 1200, 0.08],
        ] as [$name, $sym, $hash, $unit, $days, $price, $daily]) {
            MiningPackage::query()->create([
                'name' => $name,
                'asset_id' => $assets[$sym]->id,
                'hashrate' => $hash,
                'hashrate_unit' => $unit,
                'term_days' => $days,
                'price' => $price,
                'price_asset_id' => $assets['USDT']->id,
                'maintenance_fee_daily' => 0.5,
                'estimated_daily_reward' => $daily,
                'risk_disclosure' => 'Mining rewards are estimated and simulated. Not guaranteed.',
                'is_published' => true,
                'is_simulated' => true,
            ]);
        }

        InvestmentProduct::query()->create([
            'name' => 'USDT Flexible Earn',
            'slug' => 'usdt-flexible-earn',
            'description' => 'Simulated flexible earn product.',
            'asset_id' => $assets['USDT']->id,
            'apy_estimate' => 4.5,
            'lock_days' => 0,
            'min_amount' => 10,
            'risk_disclosure' => 'Yield estimates are simulated and not guaranteed.',
        ]);
        InvestmentProduct::query()->create([
            'name' => 'BTC 30-Day Vault',
            'slug' => 'btc-30d-vault',
            'description' => 'Locked simulated BTC product.',
            'asset_id' => $assets['BTC']->id,
            'apy_estimate' => 3.2,
            'lock_days' => 30,
            'min_amount' => 0.01,
            'risk_disclosure' => 'Locked funds may lose value. Not guaranteed.',
        ]);

        BillingPackage::query()->create([
            'title' => 'Basic Analyst Package',
            'slug' => 'basic-analyst',
            'description' => 'Weekly market briefings.',
            'analyst_name' => 'Alex Morgan',
            'analyst_credential' => 'Market Analyst',
            'credential_verified' => false,
            'price' => 29,
            'billing_cycle' => 'monthly',
            'features' => ['Weekly brief', 'Watchlist ideas'],
            'report_access' => 4,
            'consultation_minutes' => 0,
            'invoice_label' => 'Market Analyst Package',
            'risk_disclosure' => 'Not investment advice.',
        ]);
        BillingPackage::query()->create([
            'title' => 'Pro Analyst Package',
            'slug' => 'pro-analyst',
            'description' => 'Deeper research and office hours.',
            'analyst_name' => 'Jordan Lee',
            'analyst_credential' => 'Verified Financial Analyst',
            'credential_verified' => true,
            'price' => 99,
            'billing_cycle' => 'monthly',
            'features' => ['Daily notes', '2 reports/mo', '30 min consult'],
            'report_access' => 12,
            'consultation_minutes' => 30,
            'invoice_label' => 'Market Analyst Package',
            'risk_disclosure' => 'Not investment advice.',
        ]);
        BillingPackage::query()->create([
            'title' => 'Elite Research Package',
            'slug' => 'elite-research',
            'description' => 'Institutional-style research pack.',
            'analyst_name' => 'Sam Rivera',
            'analyst_credential' => 'CFA',
            'credential_verified' => false, // Do not claim CFA unless verified/legal
            'price' => 249,
            'billing_cycle' => 'monthly',
            'features' => ['Priority desk', 'Custom screens'],
            'report_access' => 30,
            'consultation_minutes' => 60,
            'invoice_label' => 'Market Analyst Package',
            'risk_disclosure' => 'Credential display requires verification.',
        ]);

        $collection = NftCollection::query()->create([
            'name' => 'Orbit Relics',
            'slug' => 'orbit-relics',
            'description' => 'Demo NFT collection for Bitzlatoview.',
            'floor_price' => 0.42,
            'volume_24h' => 18.5,
            'owners' => 1200,
            'items_count' => 3333,
        ]);
        NftCollection::query()->create([
            'name' => 'Gold Circuit',
            'slug' => 'gold-circuit',
            'description' => 'Second demo collection.',
            'floor_price' => 1.15,
            'volume_24h' => 42.0,
            'owners' => 880,
            'items_count' => 1000,
        ]);
        NftItem::query()->create([
            'nft_collection_id' => $collection->id,
            'owner_user_id' => $verified->id,
            'token_id' => '101',
            'name' => 'Orbit Relic #101',
            'rarity' => 'rare',
            'last_price' => 0.55,
            'attributes' => ['bg' => 'navy', 'accent' => 'gold'],
        ]);

        $cardholder = Cardholder::query()->create([
            'user_id' => $verified->id,
            'legal_name' => $verified->full_legal_name,
            'status' => 'active',
        ]);
        VirtualCard::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $verified->id,
            'cardholder_id' => $cardholder->id,
            'nickname' => 'Travel Card',
            'last_four' => '4242',
            'brand' => 'Visa',
            'currency' => 'USD',
            'spending_limit' => 2500,
            'spent_amount' => 180.25,
            'status' => 'active',
            'masked_pan' => '**** **** **** 4242',
            'is_simulated' => true,
        ]);

        TaxReport::query()->create([
            'user_id' => $verified->id,
            'tax_year' => now()->year,
            'country' => 'US',
            'cost_basis_method' => 'FIFO',
            'realized_gains' => 2450.75,
            'realized_losses' => 610.20,
            'income_total' => 155.40,
            'fees_paid' => 88.10,
            'status' => 'draft',
        ]);

        foreach ([
            ['welcome', 'Welcome email', 'Welcome to Bitzlatoview, {{name}}'],
            ['email_verification', 'Email verification', 'Verify your Bitzlatoview email'],
            ['password_reset', 'Password reset', 'Reset your Bitzlatoview password'],
            ['login_alert', 'Login alert', 'New login to your Bitzlatoview account'],
            ['kyc_submitted', 'KYC submitted', 'KYC received for {{name}}'],
            ['kyc_approved', 'KYC approved', 'Your KYC was approved'],
            ['kyc_rejected', 'KYC rejected', 'Your KYC needs attention'],
            ['deposit_received', 'Deposit received', 'Deposit credited on Bitzlatoview'],
            ['withdrawal_requested', 'Withdrawal requested', 'Withdrawal request received'],
            ['withdrawal_approved', 'Withdrawal approved', 'Withdrawal approved'],
            ['withdrawal_rejected', 'Withdrawal rejected', 'Withdrawal rejected'],
            ['p2p_order_opened', 'P2P order opened', 'P2P order opened'],
            ['p2p_order_paid', 'P2P order paid', 'P2P order marked paid'],
            ['p2p_appeal_opened', 'P2P appeal opened', 'P2P appeal opened'],
            ['virtual_card_created', 'Virtual card created', 'Virtual card created'],
            ['bot_started', 'Bot started', 'AI bot allocation started'],
            ['mining_contract_purchased', 'Mining contract purchased', 'Mining contract purchased'],
            ['tax_report_ready', 'Tax report ready', 'Your tax report is ready'],
        ] as [$key, $name, $subject]) {
            EmailTemplate::query()->create([
                'key' => $key,
                'name' => $name,
                'subject' => $subject,
                'body_html' => '<p>Hello {{name}},</p><p>'.$subject.'</p><p>— Bitzlatoview</p>',
                'category' => 'transactional',
                'is_active' => true,
            ]);
        }

        NewsArticle::query()->create([
            'title' => 'Markets open mixed as liquidity rotates into majors',
            'slug' => 'markets-open-mixed',
            'summary' => 'Simulated market wrap for BTC, ETH, and SOL.',
            'content' => 'Demo news article for Bitzlatoview.',
            'source' => 'Bitzlatoview Desk',
            'sentiment' => 'neutral',
            'asset_tags' => ['BTC', 'ETH'],
            'status' => 'published',
            'published_at' => now(),
        ]);
        NewsArticle::query()->create([
            'title' => 'P2P volumes climb in emerging corridors',
            'slug' => 'p2p-volumes-climb',
            'summary' => 'Local payment rails remain active in simulation metrics.',
            'sentiment' => 'bullish',
            'source' => 'Bitzlatoview Desk',
            'status' => 'published',
            'published_at' => now()->subHours(5),
        ]);
        NewsArticle::query()->create([
            'title' => 'Futures funding turns cautious overnight',
            'slug' => 'futures-funding-cautious',
            'summary' => 'Perpetual funding rates compress in paper markets.',
            'sentiment' => 'bearish',
            'source' => 'Bitzlatoview Desk',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        BlogPost::query()->create([
            'author_id' => $admin->id,
            'title' => 'How Bitzlatoview wallets use a double-entry ledger',
            'slug' => 'double-entry-ledger',
            'excerpt' => 'Why Primary, Trading, and Investment wallets never mutate balances directly.',
            'content' => "Every balance-changing event creates debit and credit ledger entries with idempotency keys.\n\nAdmin adjustments require maker/checker approval.",
            'category' => 'Product',
            'tags' => ['wallets', 'ledger'],
            'status' => 'published',
            'published_at' => now(),
        ]);
        BlogPost::query()->create([
            'author_id' => $admin->id,
            'title' => 'Paper trading first: compliance-aware MVP design',
            'slug' => 'paper-trading-first',
            'excerpt' => 'Why live deposits, cards, and futures stay gated until providers are connected.',
            'content' => 'Bitzlatoview ships in simulation mode for KYC, custody, brokers, card issuing, and MT5.',
            'category' => 'Compliance',
            'status' => 'published',
            'published_at' => now()->subDays(2),
        ]);

        foreach ([
            ['What is simulation mode?', 'All balances, trades, mining rewards, bot returns, and cards are simulated until licensed providers are connected.'],
            ['Are returns guaranteed?', 'No. Trading, bots, mining, copy trading, and investments can lose money.'],
            ['How do wallets work?', 'Primary, Trading, and Investment wallets update only through the double-entry ledger.'],
            ['When can I withdraw?', 'After KYC approval. Large withdrawals may require admin review.'],
            ['Is CFA research available?', 'Only verified charterholder packages may display CFA credentials with legal approval.'],
            ['Is WalletConnect custodial?', 'No. Connected external wallets are separate from Bitzlatoview ledger balances.'],
        ] as $i => [$q, $a]) {
            FaqItem::query()->create(['question' => $q, 'answer' => $a, 'sort_order' => $i + 1]);
        }

        CmsPage::query()->create([
            'title' => 'Risk Disclosure',
            'slug' => 'risk-disclosure',
            'content' => 'Trading digital and traditional assets involves substantial risk of loss.',
            'status' => 'published',
        ]);

        foreach ([['spot', 10, 10], ['swap', 20, 20], ['futures', 5, 5], ['withdrawal', 0, 0]] as [$module, $maker, $taker]) {
            FeeSchedule::query()->create([
                'name' => ucfirst($module).' fees',
                'module' => $module,
                'maker_fee_bps' => $maker,
                'taker_fee_bps' => $taker,
                'flat_fee' => $module === 'withdrawal' ? 1 : 0,
            ]);
        }

        foreach ([
            ['paper_trading', 'Paper trading mode', true],
            ['live_trading', 'Live trading', false],
            ['virtual_cards', 'Virtual cards module', true],
            ['futures', 'Futures module', true],
            ['p2p', 'P2P marketplace', true],
        ] as [$key, $name, $enabled]) {
            FeatureFlag::query()->create(['key' => $key, 'name' => $name, 'enabled' => $enabled]);
        }

        SystemSetting::query()->create(['key' => 'platform_name', 'value' => 'Bitzlatoview', 'group' => 'general']);
        SystemSetting::query()->create(['key' => 'maintenance_mode', 'value' => '0', 'group' => 'general']);
        SystemSetting::query()->create(['key' => 'default_fee_asset', 'value' => 'USDT', 'group' => 'fees']);

        // Ensure balances table has rows for display even if ledger already created them
        Balance::query()->where('available', '>', 0)->count();
    }
}
