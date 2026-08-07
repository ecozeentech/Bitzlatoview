<?php

use App\Http\Controllers\Admin\AdjustmentController as AdminAdjustmentController;
use App\Http\Controllers\Admin\AiBotController as AdminAiBotController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\BillingController as AdminBillingController;
use App\Http\Controllers\Admin\CmsController as AdminCmsController;
use App\Http\Controllers\Admin\CopyTradingController as AdminCopyTradingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\EmailController as AdminEmailController;
use App\Http\Controllers\Admin\ExtendedMarketController as AdminExtendedMarketController;
use App\Http\Controllers\Admin\StockInstrumentController as AdminStockInstrumentController;
use App\Http\Controllers\Admin\ForexPairController as AdminForexPairController;
use App\Http\Controllers\Admin\FuturesMarketController as AdminFuturesMarketController;
use App\Http\Controllers\Admin\InvestmentProductController as AdminInvestmentProductController;
use App\Http\Controllers\Admin\KycController as AdminKycController;
use App\Http\Controllers\Admin\LedgerController as AdminLedgerController;
use App\Http\Controllers\Admin\MarketController as AdminMarketController;
use App\Http\Controllers\Admin\AssetController as AdminAssetController;
use App\Http\Controllers\Admin\MiningController as AdminMiningController;
use App\Http\Controllers\Admin\Mt5Controller as AdminMt5Controller;
use App\Http\Controllers\Admin\NftController as AdminNftController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\P2PController as AdminP2PController;
use App\Http\Controllers\Admin\PaymentMethodController as AdminPaymentMethodController;
use App\Http\Controllers\Admin\RiskController as AdminRiskController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\BrandingController as AdminBrandingController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Admin\SwapController as AdminSwapController;
use App\Http\Controllers\Admin\TaxController as AdminTaxController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VirtualCardController as AdminVirtualCardController;
use App\Http\Controllers\App\AiBotController;
use App\Http\Controllers\App\BillingController;
use App\Http\Controllers\App\BuySellController;
use App\Http\Controllers\App\CopyTradingController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\ForexController;
use App\Http\Controllers\App\FundingController;
use App\Http\Controllers\App\FuturesController;
use App\Http\Controllers\App\InvestmentController;
use App\Http\Controllers\App\KycOnboardingController;
use App\Http\Controllers\App\MarketController as AppMarketController;
use App\Http\Controllers\App\MiningController;
use App\Http\Controllers\App\Mt5Controller;
use App\Http\Controllers\App\NewsBlogController as AppNewsBlogController;
use App\Http\Controllers\App\NftController;
use App\Http\Controllers\App\P2PController;
use App\Http\Controllers\App\ReferralController;
use App\Http\Controllers\App\SettingsController;
use App\Http\Controllers\App\SpotController;
use App\Http\Controllers\App\StockController;
use App\Http\Controllers\App\SupportController;
use App\Http\Controllers\App\SwapController;
use App\Http\Controllers\App\TaxController;
use App\Http\Controllers\App\VirtualCardController;
use App\Http\Controllers\App\WalletController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public marketing site
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('markets')->name('markets.')->group(function () {
    Route::get('/', [MarketController::class, 'index'])->name('index');
    Route::get('/top-gainers', [MarketController::class, 'topGainers'])->name('top-gainers');
    Route::get('/top-losers', [MarketController::class, 'topLosers'])->name('top-losers');
    Route::get('/new-listings', [MarketController::class, 'newListings'])->name('new-listings');
});

foreach (['crypto', 'stocks', 'forex', 'futures', 'nft', 'swap', 'buy-crypto', 'p2p', 'copy-trading', 'ai-trading-bot', 'mining', 'investments', 'metatrader-5'] as $slug) {
    Route::get('/'.$slug, [LandingController::class, 'show'])->defaults('slug', $slug)->name('landing.'.$slug);
}

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/faq', [FaqController::class, 'index'])->name('faq');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::view('/about', 'public.pages.about')->name('about');
Route::view('/fees', 'public.pages.fees')->name('fees');
Route::view('/proof-of-reserves', 'public.pages.proof-of-reserves')->name('proof-of-reserves');
Route::view('/security', 'public.pages.security')->name('security');
Route::view('/api-docs', 'public.pages.api-docs')->name('api-docs');
Route::view('/affiliate', 'public.pages.affiliate')->name('affiliate');
Route::view('/terms', 'public.pages.terms')->name('terms');
Route::view('/privacy', 'public.pages.privacy')->name('privacy');
Route::view('/risk-disclosure', 'public.pages.risk-disclosure')->name('risk-disclosure');
Route::view('/aml-kyc-policy', 'public.pages.aml-kyc-policy')->name('aml-kyc-policy');
Route::view('/cookie-policy', 'public.pages.cookie-policy')->name('cookie-policy');

Route::get('/referrals', [HomeController::class, 'referralRedirect'])->name('referrals.public');

/*
|--------------------------------------------------------------------------
| Auth (Breeze) + KYC onboarding + verification gate
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/kyc-onboarding', [KycOnboardingController::class, 'create'])->name('kyc-onboarding');
    Route::post('/kyc-onboarding', [KycOnboardingController::class, 'store'])->name('kyc-onboarding.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| User application (/app/*)
|--------------------------------------------------------------------------
*/

Route::prefix('app')->name('app.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/markets', [AppMarketController::class, 'index'])->name('markets');
    Route::post('/markets/{marketPair}/watchlist', [AppMarketController::class, 'toggleWatchlist'])->name('markets.watchlist');

    Route::get('/spot', [SpotController::class, 'index'])->name('spot');
    Route::get('/spot/{symbol}', [SpotController::class, 'show'])->name('spot.show');
    Route::post('/spot/{symbol}/orders', [SpotController::class, 'store'])->name('spot.orders.store');
    Route::post('/spot/orders/{order}/cancel', [SpotController::class, 'cancel'])->name('spot.orders.cancel');

    Route::get('/buy-sell', [BuySellController::class, 'index'])->name('buy-sell');
    Route::post('/buy-sell', [BuySellController::class, 'store'])->name('buy-sell.store');

    Route::get('/swap', [SwapController::class, 'index'])->name('swap');
    Route::post('/swap/quote', [SwapController::class, 'quote'])->name('swap.quote');
    Route::post('/swap', [SwapController::class, 'store'])->name('swap.store');

    // Wallets
    Route::get('/wallet/{type}', [WalletController::class, 'show'])->name('wallet.show')->whereIn('type', ['primary', 'trading', 'investment']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer');

    Route::get('/funding/deposit', [FundingController::class, 'deposit'])->name('funding.deposit');
    Route::post('/funding/deposit', [FundingController::class, 'storeDeposit'])->name('funding.deposit.store');
    Route::get('/funding/withdraw', [FundingController::class, 'withdraw'])->middleware('kyc.approved')->name('funding.withdraw');
    Route::post('/funding/withdraw', [FundingController::class, 'storeWithdraw'])->middleware('kyc.approved')->name('funding.withdraw.store');
    Route::post('/funding/address-book', [FundingController::class, 'storeAddress'])->name('funding.address-book.store');
    Route::get('/funding/transactions', [FundingController::class, 'transactions'])->name('funding.transactions');

    // P2P
    Route::prefix('p2p')->name('p2p.')->group(function () {
        // The sidebar links to the bare '/app/p2p' prefix (see partials.app-sidebar), but only
        // sub-paths like /buy and /sell were ever registered — that made the main "P2P" nav
        // link 404 for every user. Route the index straight to the Buy Crypto view.
        Route::get('/', [P2PController::class, 'buy'])->name('index');
        Route::get('/buy', [P2PController::class, 'buy'])->name('buy');
        Route::get('/sell', [P2PController::class, 'sell'])->name('sell');
        Route::get('/orders', [P2PController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [P2PController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders', [P2PController::class, 'createOrder'])->middleware('kyc.approved')->name('orders.create');
        Route::post('/orders/{order}/mark-paid', [P2PController::class, 'markPaid'])->name('orders.mark-paid');
        Route::post('/orders/{order}/release', [P2PController::class, 'release'])->name('orders.release');
        Route::post('/orders/{order}/cancel', [P2PController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('/orders/{order}/messages', [P2PController::class, 'sendMessage'])->name('orders.messages');
        Route::post('/orders/{order}/appeal', [P2PController::class, 'appeal'])->name('orders.appeal');
        Route::get('/ads', [P2PController::class, 'myAds'])->name('ads');
        Route::post('/ads', [P2PController::class, 'storeAd'])->middleware('kyc.approved')->name('ads.store');
        Route::patch('/ads/{ad}', [P2PController::class, 'updateAd'])->name('ads.update');
        Route::get('/merchant', [P2PController::class, 'merchant'])->name('merchant');
        Route::post('/merchant', [P2PController::class, 'applyMerchant'])->middleware('kyc.approved')->name('merchant.apply');
        Route::post('/payment-methods', [P2PController::class, 'storePaymentMethod'])->name('payment-methods.store');
        Route::get('/appeals', [P2PController::class, 'appeals'])->name('appeals');
    });

    // Copy trading
    Route::prefix('copy-trading')->name('copy-trading.')->group(function () {
        Route::get('/', [CopyTradingController::class, 'index'])->name('index');
        Route::get('/traders', [CopyTradingController::class, 'traders'])->name('traders');
        Route::get('/traders/{trader}', [CopyTradingController::class, 'show'])->name('traders.show');
        Route::get('/my-copies', [CopyTradingController::class, 'myCopies'])->name('my-copies');
        Route::post('/traders/{trader}/allocate', [CopyTradingController::class, 'allocate'])->name('allocate');
        Route::post('/allocations/{allocation}/pause', [CopyTradingController::class, 'pause'])->name('pause');
        Route::post('/allocations/{allocation}/resume', [CopyTradingController::class, 'resume'])->name('resume');
        Route::post('/allocations/{allocation}/stop', [CopyTradingController::class, 'stop'])->name('stop');
    });

    // AI bots
    Route::prefix('ai-bots')->name('ai-bots.')->group(function () {
        Route::get('/', [AiBotController::class, 'index'])->name('index');
        Route::get('/marketplace', [AiBotController::class, 'marketplace'])->name('marketplace');
        Route::get('/my-bots', [AiBotController::class, 'myBots'])->name('my-bots');
        Route::get('/{bot}', [AiBotController::class, 'show'])->name('show');
        Route::post('/{bot}/allocate', [AiBotController::class, 'allocate'])->name('allocate');
        Route::post('/allocations/{allocation}/pause', [AiBotController::class, 'pause'])->name('pause');
        Route::post('/allocations/{allocation}/resume', [AiBotController::class, 'resume'])->name('resume');
        Route::post('/allocations/{allocation}/stop', [AiBotController::class, 'stop'])->name('stop');
    });

    // Mining
    Route::prefix('mining')->name('mining.')->group(function () {
        Route::get('/', [MiningController::class, 'index'])->name('index');
        Route::get('/contracts', [MiningController::class, 'contracts'])->name('contracts');
        Route::get('/rewards', [MiningController::class, 'rewards'])->name('rewards');
        Route::post('/packages/{package}/purchase', [MiningController::class, 'purchase'])->name('purchase');
    });

    // Investments
    Route::prefix('investments')->name('investments.')->group(function () {
        Route::get('/', [InvestmentController::class, 'index'])->name('index');
        Route::post('/products/{product}/subscribe', [InvestmentController::class, 'subscribe'])->name('subscribe');
        Route::post('/subscriptions/{subscription}/redeem', [InvestmentController::class, 'redeem'])->name('redeem');
    });

    // Stocks / Forex / Futures / MT5
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks');
    Route::post('/stocks/{instrument}/orders', [StockController::class, 'store'])->middleware('kyc.approved')->name('stocks.orders.store');

    Route::get('/forex', [ForexController::class, 'index'])->name('forex');
    Route::post('/forex/{pair}/orders', [ForexController::class, 'store'])->middleware('kyc.approved')->name('forex.orders.store');
    Route::post('/forex/positions/{position}/close', [ForexController::class, 'close'])->name('forex.positions.close');

    Route::get('/futures', [FuturesController::class, 'index'])->name('futures');
    Route::post('/futures/{market}/positions', [FuturesController::class, 'store'])->middleware('kyc.approved')->name('futures.positions.store');
    Route::post('/futures/positions/{position}/close', [FuturesController::class, 'close'])->name('futures.positions.close');

    Route::get('/metatrader-5', [Mt5Controller::class, 'index'])->name('mt5');
    Route::post('/metatrader-5/connect', [Mt5Controller::class, 'connect'])->middleware('kyc.approved')->name('mt5.connect');
    Route::post('/metatrader-5/{account}/sync', [Mt5Controller::class, 'sync'])->name('mt5.sync');
    Route::post('/metatrader-5/{account}/disconnect', [Mt5Controller::class, 'disconnect'])->name('mt5.disconnect');

    // NFT
    Route::prefix('nft')->name('nft.')->group(function () {
        Route::get('/', [NftController::class, 'index'])->name('index');
        Route::get('/collections/{collection:slug}', [NftController::class, 'showCollection'])->name('collections.show');
        Route::get('/items/{item}', [NftController::class, 'showItem'])->name('items.show');
        Route::get('/my-nfts', [NftController::class, 'myNfts'])->name('my-nfts');
        Route::post('/items/{item}/buy', [NftController::class, 'buy'])->middleware('kyc.approved')->name('items.buy');
        Route::post('/items/{item}/list', [NftController::class, 'list'])->name('items.list');
        Route::post('/items/{item}/bid', [NftController::class, 'bid'])->middleware('kyc.approved')->name('items.bid');
    });

    // Virtual cards
    Route::prefix('virtual-cards')->name('virtual-cards.')->group(function () {
        Route::get('/', [VirtualCardController::class, 'index'])->name('index');
        Route::post('/', [VirtualCardController::class, 'store'])->middleware('kyc.approved')->name('store');
        Route::post('/{card}/freeze', [VirtualCardController::class, 'freeze'])->name('freeze');
        Route::post('/{card}/unfreeze', [VirtualCardController::class, 'unfreeze'])->name('unfreeze');
        Route::post('/{card}/limit', [VirtualCardController::class, 'updateLimit'])->name('limit');
        Route::post('/{card}/fund', [VirtualCardController::class, 'fund'])->name('fund');
        Route::post('/{card}/reveal', [VirtualCardController::class, 'reveal'])->name('reveal');
        Route::delete('/{card}', [VirtualCardController::class, 'cancel'])->name('cancel');
    });

    // Tax
    Route::prefix('tax')->name('tax.')->group(function () {
        Route::get('/', [TaxController::class, 'index'])->name('index');
        Route::post('/reports', [TaxController::class, 'generate'])->name('reports.generate');
        Route::get('/reports/{report}/export', [TaxController::class, 'export'])->name('reports.export');
    });

    // Analyst / billing packages
    Route::prefix('analyst-packages')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::post('/{package}/subscribe', [BillingController::class, 'subscribe'])->name('subscribe');
        Route::post('/subscriptions/{subscription}/cancel', [BillingController::class, 'cancel'])->name('cancel');
        Route::get('/invoices/{invoice}', [BillingController::class, 'invoice'])->name('invoice');
    });

    Route::get('/news', [AppNewsBlogController::class, 'news'])->name('news');
    Route::get('/blog', [AppNewsBlogController::class, 'blog'])->name('blog');

    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals');

    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::post('/', [SupportController::class, 'store'])->name('store');
        Route::get('/{ticket}', [SupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/messages', [SupportController::class, 'reply'])->name('reply');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'profile'])->name('index');
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::patch('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::post('/security/2fa/enable', [SettingsController::class, 'enable2fa'])->name('2fa.enable');
        Route::post('/security/2fa/disable', [SettingsController::class, 'disable2fa'])->name('2fa.disable');
        Route::get('/kyc', [SettingsController::class, 'kyc'])->name('kyc');
        Route::post('/kyc', [SettingsController::class, 'submitKyc'])->name('kyc.submit');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/api-keys', [SettingsController::class, 'apiKeys'])->name('api-keys');
        Route::post('/api-keys', [SettingsController::class, 'generateApiKey'])->name('api-keys.generate');
        Route::delete('/api-keys', [SettingsController::class, 'revokeApiKey'])->name('api-keys.revoke');
        Route::get('/wallet-connect', [SettingsController::class, 'walletConnect'])->name('wallet-connect');
        Route::post('/wallet-connect', [SettingsController::class, 'connectWallet'])->name('wallet-connect.store');
        Route::delete('/wallet-connect/{id}', [SettingsController::class, 'disconnectWallet'])->name('wallet-connect.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Admin dashboard (/admin/*)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('home');
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/unsuspend', [AdminUserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('/users/{user}/notes', [AdminUserController::class, 'addNote'])->name('users.notes.store');
    Route::post('/users/{user}/force-password-reset', [AdminUserController::class, 'forcePasswordReset'])->name('users.force-password-reset');

    Route::get('/kyc', [AdminKycController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/{submission}', [AdminKycController::class, 'show'])->name('kyc.show');
    Route::get('/kyc/{submission}/document/{field}', [AdminKycController::class, 'document'])->name('kyc.document');
    Route::post('/kyc/{submission}/approve', [AdminKycController::class, 'approve'])->name('kyc.approve');
    Route::post('/kyc/{submission}/reject', [AdminKycController::class, 'reject'])->name('kyc.reject');
    Route::post('/kyc/{submission}/more-info', [AdminKycController::class, 'moreInfo'])->name('kyc.more-info');

    Route::get('/risk', [AdminRiskController::class, 'index'])->name('risk.index');
    Route::post('/risk/alerts/{alert}/resolve', [AdminRiskController::class, 'resolveAlert'])->name('risk.alerts.resolve');
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');

    Route::get('/payment-methods', [AdminPaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::post('/payment-methods', [AdminPaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::patch('/payment-methods/{paymentMethod}', [AdminPaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::post('/payment-methods/{paymentMethod}/toggle', [AdminPaymentMethodController::class, 'toggle'])->name('payment-methods.toggle');
    Route::delete('/payment-methods/{paymentMethod}', [AdminPaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

    Route::get('/deposits', [AdminDepositController::class, 'index'])->name('deposits.index');
    Route::get('/deposits/{deposit}/proof', [AdminDepositController::class, 'proof'])->name('deposits.proof');
    Route::post('/deposits/{deposit}/credit', [AdminDepositController::class, 'credit'])->name('deposits.credit');
    Route::post('/deposits/{deposit}/reject', [AdminDepositController::class, 'reject'])->name('deposits.reject');

    Route::get('/withdrawals', [AdminDepositController::class, 'withdrawals'])->name('withdrawals.index');
    Route::post('/withdrawals/{withdrawal}/approve', [AdminDepositController::class, 'approveWithdrawal'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/complete', [AdminDepositController::class, 'completeWithdrawal'])->name('withdrawals.complete');
    Route::post('/withdrawals/{withdrawal}/reject', [AdminDepositController::class, 'rejectWithdrawal'])->name('withdrawals.reject');

    Route::get('/ledger', [AdminLedgerController::class, 'index'])->name('ledger.index');

    Route::get('/adjustments', [AdminAdjustmentController::class, 'index'])->name('adjustments.index');
    Route::post('/adjustments', [AdminAdjustmentController::class, 'store'])->name('adjustments.store');
    Route::post('/adjustments/{adjustment}/approve', [AdminAdjustmentController::class, 'approve'])->name('adjustments.approve');
    Route::post('/adjustments/{adjustment}/reject', [AdminAdjustmentController::class, 'reject'])->name('adjustments.reject');

    Route::get('/markets', [AdminMarketController::class, 'index'])->name('markets.index');
    Route::post('/markets', [AdminMarketController::class, 'store'])->name('markets.store');
    Route::patch('/markets/{pair}', [AdminMarketController::class, 'update'])->name('markets.update');

    Route::post('/assets', [AdminAssetController::class, 'store'])->name('assets.store');
    Route::put('/assets/{asset}', [AdminAssetController::class, 'update'])->name('assets.update');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/swap', [AdminSwapController::class, 'index'])->name('swap.index');

    Route::get('/p2p', [AdminP2PController::class, 'index'])->name('p2p.index');
    Route::get('/p2p/orders', [AdminP2PController::class, 'orders'])->name('p2p.orders');
    Route::get('/p2p/ads', [AdminP2PController::class, 'ads'])->name('p2p.ads');
    Route::get('/p2p/appeals', [AdminP2PController::class, 'appeals'])->name('p2p.appeals');
    Route::post('/p2p/appeals/{appeal}/resolve', [AdminP2PController::class, 'resolveAppeal'])->name('p2p.appeals.resolve');

    Route::get('/copy-trading', [AdminCopyTradingController::class, 'index'])->name('copy-trading.index');
    Route::post('/copy-trading/traders', [AdminCopyTradingController::class, 'store'])->name('copy-trading.store');
    Route::patch('/copy-trading/traders/{trader}', [AdminCopyTradingController::class, 'update'])->name('copy-trading.update');

    Route::get('/ai-bots', [AdminAiBotController::class, 'index'])->name('ai-bots.index');
    Route::post('/ai-bots', [AdminAiBotController::class, 'store'])->name('ai-bots.store');
    Route::patch('/ai-bots/{bot}', [AdminAiBotController::class, 'update'])->name('ai-bots.update');

    Route::get('/mining', [AdminMiningController::class, 'index'])->name('mining.index');
    Route::post('/mining/packages', [AdminMiningController::class, 'store'])->name('mining.store');
    Route::patch('/mining/packages/{package}', [AdminMiningController::class, 'update'])->name('mining.update');

    Route::get('/investments', [AdminInvestmentProductController::class, 'index'])->name('investments.index');
    Route::post('/investments', [AdminInvestmentProductController::class, 'store'])->name('investments.store');
    Route::put('/investments/{product}', [AdminInvestmentProductController::class, 'update'])->name('investments.update');
    Route::post('/investments/{product}/toggle', [AdminInvestmentProductController::class, 'toggle'])->name('investments.toggle');
    Route::delete('/investments/{product}', [AdminInvestmentProductController::class, 'destroy'])->name('investments.destroy');
    Route::get('/markets-extended', [AdminExtendedMarketController::class, 'index'])->name('markets-extended.index');

    Route::post('/stocks', [AdminStockInstrumentController::class, 'store'])->name('stocks.store');
    Route::put('/stocks/{instrument}', [AdminStockInstrumentController::class, 'update'])->name('stocks.update');
    Route::delete('/stocks/{instrument}', [AdminStockInstrumentController::class, 'destroy'])->name('stocks.destroy');
    Route::post('/stocks/import', [AdminStockInstrumentController::class, 'importCsv'])->name('stocks.import');

    Route::post('/forex-pairs', [AdminForexPairController::class, 'store'])->name('forex-pairs.store');
    Route::put('/forex-pairs/{pair}', [AdminForexPairController::class, 'update'])->name('forex-pairs.update');
    Route::delete('/forex-pairs/{pair}', [AdminForexPairController::class, 'destroy'])->name('forex-pairs.destroy');

    Route::post('/futures-markets', [AdminFuturesMarketController::class, 'store'])->name('futures-markets.store');
    Route::put('/futures-markets/{market}', [AdminFuturesMarketController::class, 'update'])->name('futures-markets.update');
    Route::delete('/futures-markets/{market}', [AdminFuturesMarketController::class, 'destroy'])->name('futures-markets.destroy');
    Route::get('/metatrader', [AdminMt5Controller::class, 'index'])->name('metatrader.index');

    Route::get('/nft', [AdminNftController::class, 'index'])->name('nft.index');
    Route::post('/nft/collections', [AdminNftController::class, 'storeCollection'])->name('nft.collections.store');
    Route::put('/nft/collections/{collection}', [AdminNftController::class, 'updateCollection'])->name('nft.collections.update');
    Route::delete('/nft/collections/{collection}', [AdminNftController::class, 'destroyCollection'])->name('nft.collections.destroy');
    Route::post('/nft/collections/{collection}/items', [AdminNftController::class, 'storeItem'])->name('nft.items.store');
    Route::put('/nft/items/{item}', [AdminNftController::class, 'updateItem'])->name('nft.items.update');
    Route::delete('/nft/items/{item}', [AdminNftController::class, 'destroyItem'])->name('nft.items.destroy');
    Route::get('/virtual-cards', [AdminVirtualCardController::class, 'index'])->name('virtual-cards.index');
    Route::post('/virtual-cards/settings', [AdminVirtualCardController::class, 'updateSettings'])->name('virtual-cards.settings.update');
    Route::post('/virtual-cards/{card}/approve', [AdminVirtualCardController::class, 'approve'])->name('virtual-cards.approve');
    Route::post('/virtual-cards/{card}/reject', [AdminVirtualCardController::class, 'reject'])->name('virtual-cards.reject');
    Route::post('/virtual-cards/{card}/freeze', [AdminVirtualCardController::class, 'freeze'])->name('virtual-cards.freeze');
    Route::post('/virtual-cards/{card}/unfreeze', [AdminVirtualCardController::class, 'unfreeze'])->name('virtual-cards.unfreeze');

    Route::get('/tax', [AdminTaxController::class, 'index'])->name('tax.index');
    Route::get('/billing', [AdminBillingController::class, 'index'])->name('billing.index');
    Route::post('/billing', [AdminBillingController::class, 'store'])->name('billing.store');
    Route::patch('/billing/{package}', [AdminBillingController::class, 'update'])->name('billing.update');
    Route::post('/billing/analysts/{analyst}/verify', [AdminBillingController::class, 'verifyAnalyst'])->name('billing.analysts.verify');

    Route::get('/blog', [AdminCmsController::class, 'blog'])->name('blog.index');
    Route::post('/blog', [AdminCmsController::class, 'storeBlog'])->name('blog.store');
    Route::patch('/blog/{post}', [AdminCmsController::class, 'updateBlog'])->name('blog.update');
    Route::delete('/blog/{post}', [AdminCmsController::class, 'destroyBlog'])->name('blog.destroy');

    Route::get('/news', [AdminCmsController::class, 'news'])->name('news.index');
    Route::post('/news', [AdminCmsController::class, 'storeNews'])->name('news.store');
    Route::delete('/news/{article}', [AdminCmsController::class, 'destroyNews'])->name('news.destroy');

    Route::get('/faq', [AdminCmsController::class, 'faq'])->name('faq.index');
    Route::post('/faq', [AdminCmsController::class, 'storeFaq'])->name('faq.store');
    Route::delete('/faq/{faq}', [AdminCmsController::class, 'destroyFaq'])->name('faq.destroy');

    Route::get('/cms', [AdminCmsController::class, 'pages'])->name('cms.index');
    Route::post('/cms', [AdminCmsController::class, 'storePage'])->name('cms.store');

    Route::get('/email/templates', [AdminEmailController::class, 'templates'])->name('email.templates');
    Route::patch('/email/templates/{template}', [AdminEmailController::class, 'updateTemplate'])->name('email.templates.update');
    Route::get('/email/campaigns', [AdminEmailController::class, 'campaigns'])->name('email.campaigns');
    Route::post('/email/campaigns', [AdminEmailController::class, 'storeCampaign'])->name('email.campaigns.store');
    Route::post('/email/campaigns/{campaign}/send-test', [AdminEmailController::class, 'sendTest'])->name('email.campaigns.send-test');
    Route::post('/email/campaigns/{campaign}/send', [AdminEmailController::class, 'send'])->name('email.campaigns.send');
    Route::get('/email/logs', [AdminEmailController::class, 'logs'])->name('email.logs');

    Route::get('/support', [AdminSupportController::class, 'index'])->name('support.index');
    Route::get('/support/{ticket}', [AdminSupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('support.reply');
    Route::post('/support/{ticket}/close', [AdminSupportController::class, 'close'])->name('support.close');

    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/feature-flags', [AdminSettingsController::class, 'featureFlags'])->name('settings.feature-flags');
    Route::post('/settings/feature-flags/{flag}/toggle', [AdminSettingsController::class, 'toggleFlag'])->name('settings.feature-flags.toggle');

    Route::get('/settings/branding', [AdminBrandingController::class, 'edit'])->name('settings.branding');
    Route::post('/settings/branding', [AdminBrandingController::class, 'update'])->name('settings.branding.update');
    Route::post('/settings/branding/reset-logo', [AdminBrandingController::class, 'resetLogo'])->name('settings.branding.reset-logo');
    Route::post('/settings/branding/reset-favicon', [AdminBrandingController::class, 'resetFavicon'])->name('settings.branding.reset-favicon');
});
