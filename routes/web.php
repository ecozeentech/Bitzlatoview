<?php

use App\Http\Controllers\Admin\AdminResourceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\InvestController;
use App\Http\Controllers\App\MarketsExtraController;
use App\Http\Controllers\App\P2PController;
use App\Http\Controllers\App\SettingsController;
use App\Http\Controllers\App\TradingController;
use App\Http\Controllers\App\WalletController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/markets', [SiteController::class, 'markets'])->name('markets');
Route::get('/markets/top-gainers', [SiteController::class, 'topGainers'])->name('markets.top-gainers');
Route::get('/markets/top-losers', [SiteController::class, 'topLosers'])->name('markets.top-losers');
Route::get('/markets/new-listings', [SiteController::class, 'newListings'])->name('markets.new-listings');
Route::get('/crypto', [SiteController::class, 'crypto'])->name('crypto');
Route::get('/stocks', [SiteController::class, 'stocks'])->name('stocks');
Route::get('/forex', [SiteController::class, 'forex'])->name('forex');
Route::get('/futures', [SiteController::class, 'futures'])->name('futures');
Route::get('/nft', [SiteController::class, 'nft'])->name('nft');
Route::get('/swap', [SiteController::class, 'swap'])->name('swap');
Route::get('/buy-crypto', [SiteController::class, 'buyCrypto'])->name('buy-crypto');
Route::get('/p2p', [SiteController::class, 'p2p'])->name('p2p');
Route::get('/copy-trading', [SiteController::class, 'copyTrading'])->name('copy-trading');
Route::get('/ai-trading-bot', [SiteController::class, 'aiTradingBot'])->name('ai-trading-bot');
Route::get('/mining', [SiteController::class, 'mining'])->name('mining');
Route::get('/investments', [SiteController::class, 'investments'])->name('investments');
Route::get('/metatrader-5', [SiteController::class, 'metatrader5'])->name('metatrader-5');
Route::get('/news', [SiteController::class, 'news'])->name('news');
Route::get('/blog', [SiteController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [SiteController::class, 'blogShow'])->name('blog.show');
Route::get('/about', [SiteController::class, 'about'])->name('about');
Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
Route::post('/contact', [SiteController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/faq', [SiteController::class, 'faq'])->name('faq');
Route::get('/fees', [SiteController::class, 'fees'])->name('fees');
Route::get('/proof-of-reserves', [SiteController::class, 'proofOfReserves'])->name('proof-of-reserves');
Route::get('/security', [SiteController::class, 'security'])->name('security');
Route::get('/api-docs', [SiteController::class, 'apiDocs'])->name('api-docs');
Route::get('/affiliate', [SiteController::class, 'affiliate'])->name('affiliate');
Route::get('/referrals', [SiteController::class, 'referrals'])->name('referrals');
Route::get('/terms', [SiteController::class, 'terms'])->name('terms');
Route::get('/privacy', [SiteController::class, 'privacy'])->name('privacy');
Route::get('/risk-disclosure', [SiteController::class, 'riskDisclosure'])->name('risk-disclosure');
Route::get('/aml-kyc-policy', [SiteController::class, 'amlKycPolicy'])->name('aml-kyc-policy');
Route::get('/cookie-policy', [SiteController::class, 'cookiePolicy'])->name('cookie-policy');

Route::redirect('/dashboard', '/app/dashboard');

Route::middleware(['auth', 'verified', 'active'])->prefix('app')->name('app.')->group(function () {
    Route::get('/', fn () => redirect()->route('app.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/markets', [TradingController::class, 'markets'])->name('markets');

    Route::get('/spot', [TradingController::class, 'spotIndex'])->name('spot');
    Route::get('/spot/{symbol}', [TradingController::class, 'spotShow'])->name('spot.show');
    Route::post('/spot/{symbol}/orders', [TradingController::class, 'placeOrder'])->name('spot.order');
    Route::delete('/orders/{id}', [TradingController::class, 'cancelOrder'])->name('orders.cancel');

    Route::get('/buy-sell', [TradingController::class, 'buySell'])->name('buy-sell');
    Route::post('/buy-sell', [TradingController::class, 'buySellExecute'])->name('buy-sell.execute');

    Route::get('/swap', [TradingController::class, 'swap'])->name('swap');
    Route::post('/swap/quote', [TradingController::class, 'swapQuote'])->name('swap.quote');
    Route::post('/swap/execute', [TradingController::class, 'swapExecute'])->name('swap.execute');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
    Route::get('/wallet/{type}', [WalletController::class, 'show'])->name('wallet.show')->where('type', 'primary|trading|investment');
    Route::get('/wallet/deposit', [WalletController::class, 'depositForm'])->name('wallet.deposit');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit.store');
    Route::get('/wallet/withdraw', [WalletController::class, 'withdrawForm'])->name('wallet.withdraw');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw.store');
    Route::get('/wallet/transfer', [WalletController::class, 'transferForm'])->name('wallet.transfer');
    Route::post('/wallet/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer.store');
    Route::get('/wallet/history', [WalletController::class, 'history'])->name('wallet.history');

    Route::redirect('/funding', '/app/wallet');
    Route::redirect('/funding/deposit', '/app/wallet/deposit');
    Route::redirect('/funding/withdraw', '/app/wallet/withdraw');
    Route::redirect('/funding/transactions', '/app/wallet/history');

    Route::get('/p2p', [P2PController::class, 'index'])->name('p2p');
    Route::get('/p2p/buy', [P2PController::class, 'buy'])->name('p2p.buy');
    Route::get('/p2p/sell', [P2PController::class, 'sell'])->name('p2p.sell');
    Route::post('/p2p/ads/{adId}/orders', [P2PController::class, 'createOrder'])->name('p2p.order');
    Route::get('/p2p/orders', [P2PController::class, 'orders'])->name('p2p.orders');
    Route::post('/p2p/orders/{id}/mark-paid', [P2PController::class, 'markPaid'])->name('p2p.mark-paid');
    Route::post('/p2p/orders/{id}/release', [P2PController::class, 'release'])->name('p2p.release');
    Route::post('/p2p/orders/{id}/cancel', [P2PController::class, 'cancel'])->name('p2p.cancel');
    Route::post('/p2p/orders/{id}/appeal', [P2PController::class, 'appeal'])->name('p2p.appeal');
    Route::post('/p2p/orders/{id}/messages', [P2PController::class, 'message'])->name('p2p.message');
    Route::get('/p2p/ads', [P2PController::class, 'ads'])->name('p2p.ads');
    Route::post('/p2p/ads', [P2PController::class, 'storeAd'])->name('p2p.ads.store');
    Route::get('/p2p/merchant', [P2PController::class, 'merchant'])->name('p2p.merchant');
    Route::get('/p2p/appeals', [P2PController::class, 'appeals'])->name('p2p.appeals');

    Route::get('/copy-trading', [InvestController::class, 'copyTrading'])->name('copy-trading');
    Route::get('/copy-trading/traders', [InvestController::class, 'traders'])->name('copy-trading.traders');
    Route::get('/copy-trading/my-copies', [InvestController::class, 'myCopies'])->name('copy-trading.my-copies');
    Route::post('/copy-trading/traders/{traderId}/allocate', [InvestController::class, 'allocateCopy'])->name('copy-trading.allocate');
    Route::post('/copy-trading/{id}/pause', [InvestController::class, 'pauseCopy'])->name('copy-trading.pause');
    Route::post('/copy-trading/{id}/stop', [InvestController::class, 'stopCopy'])->name('copy-trading.stop');

    Route::get('/ai-bots', [InvestController::class, 'aiBots'])->name('ai-bots');
    Route::get('/ai-bots/marketplace', [InvestController::class, 'aiMarketplace'])->name('ai-bots.marketplace');
    Route::get('/ai-bots/my-bots', [InvestController::class, 'myBots'])->name('ai-bots.my-bots');
    Route::post('/ai-bots/{botId}/allocate', [InvestController::class, 'allocateBot'])->name('ai-bots.allocate');
    Route::post('/ai-bots/allocations/{id}/pause', [InvestController::class, 'pauseBot'])->name('ai-bots.pause');
    Route::post('/ai-bots/allocations/{id}/stop', [InvestController::class, 'stopBot'])->name('ai-bots.stop');

    Route::get('/mining', [InvestController::class, 'mining'])->name('mining');
    Route::get('/mining/contracts', [InvestController::class, 'miningContracts'])->name('mining.contracts');
    Route::get('/mining/rewards', [InvestController::class, 'miningRewards'])->name('mining.rewards');
    Route::post('/mining/{packageId}/purchase', [InvestController::class, 'purchaseMining'])->name('mining.purchase');

    Route::get('/investments', [InvestController::class, 'investments'])->name('investments');
    Route::post('/investments/{productId}/subscribe', [InvestController::class, 'subscribeInvestment'])->name('investments.subscribe');
    Route::get('/analyst-packages', [MarketsExtraController::class, 'analystPackages'])->name('analyst-packages');
    Route::post('/analyst-packages/{id}/purchase', [MarketsExtraController::class, 'purchasePackage'])->name('analyst-packages.purchase');

    Route::get('/stocks', [MarketsExtraController::class, 'stocks'])->name('stocks');
    Route::post('/stocks/orders', [MarketsExtraController::class, 'stockOrder'])->name('stocks.order');
    Route::get('/forex', [MarketsExtraController::class, 'forex'])->name('forex');
    Route::post('/forex/orders', [MarketsExtraController::class, 'forexOrder'])->name('forex.order');
    Route::get('/futures', [MarketsExtraController::class, 'futures'])->name('futures');
    Route::post('/futures/agreement', [MarketsExtraController::class, 'acceptFuturesAgreement'])->name('futures.agreement');
    Route::post('/futures/orders', [MarketsExtraController::class, 'futuresOrder'])->name('futures.order');
    Route::get('/metatrader-5', [MarketsExtraController::class, 'metatrader'])->name('metatrader');
    Route::post('/metatrader-5/connect', [MarketsExtraController::class, 'connectMt5'])->name('metatrader.connect');

    Route::get('/nft', [MarketsExtraController::class, 'nft'])->name('nft');
    Route::get('/nft/collections', [MarketsExtraController::class, 'nftCollections'])->name('nft.collections');
    Route::get('/nft/my-nfts', [MarketsExtraController::class, 'myNfts'])->name('nft.my');

    Route::get('/virtual-cards', [MarketsExtraController::class, 'virtualCards'])->name('virtual-cards');
    Route::post('/virtual-cards', [MarketsExtraController::class, 'createCard'])->name('virtual-cards.create');
    Route::post('/virtual-cards/{id}/freeze', [MarketsExtraController::class, 'freezeCard'])->name('virtual-cards.freeze');
    Route::get('/virtual-cards/{id}/transactions', [MarketsExtraController::class, 'cardTransactions'])->name('virtual-cards.transactions');

    Route::get('/tax', [MarketsExtraController::class, 'tax'])->name('tax');
    Route::get('/news', [SettingsController::class, 'news'])->name('news');
    Route::get('/blog', [SettingsController::class, 'blog'])->name('blog');
    Route::get('/referrals', [SettingsController::class, 'referrals'])->name('referrals');
    Route::get('/support', [SettingsController::class, 'support'])->name('support');
    Route::post('/support', [SettingsController::class, 'openTicket'])->name('support.open');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::get('/settings/security', [SettingsController::class, 'security'])->name('settings.security');
    Route::post('/settings/security/2fa', [SettingsController::class, 'enable2fa'])->name('settings.2fa');
    Route::post('/settings/security/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::get('/settings/kyc', [SettingsController::class, 'kyc'])->name('settings.kyc');
    Route::post('/settings/kyc', [SettingsController::class, 'submitKyc'])->name('settings.kyc.submit');
    Route::get('/settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::get('/settings/api-keys', [SettingsController::class, 'apiKeys'])->name('settings.api-keys');
    Route::get('/settings/wallet-connect', [MarketsExtraController::class, 'walletConnect'])->name('settings.wallet-connect');
    Route::post('/settings/wallet-connect', [MarketsExtraController::class, 'connectExternalWallet'])->name('settings.wallet-connect.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminResourceController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminResourceController::class, 'userShow'])->name('users.show');
    Route::patch('/users/{id}', [AdminResourceController::class, 'userUpdate'])->name('users.update');
    Route::get('/kyc', [AdminResourceController::class, 'kyc'])->name('kyc');
    Route::patch('/kyc/{id}', [AdminResourceController::class, 'kycReview'])->name('kyc.review');
    Route::get('/deposits', [AdminResourceController::class, 'deposits'])->name('deposits');
    Route::get('/withdrawals', [AdminResourceController::class, 'withdrawals'])->name('withdrawals');
    Route::patch('/withdrawals/{id}', [AdminResourceController::class, 'withdrawalAction'])->name('withdrawals.action');
    Route::get('/ledger', [AdminResourceController::class, 'ledger'])->name('ledger');
    Route::get('/orders', [AdminResourceController::class, 'orders'])->name('orders');
    Route::get('/trades', [AdminResourceController::class, 'trades'])->name('trades');
    Route::get('/markets', [AdminResourceController::class, 'markets'])->name('markets');
    Route::get('/p2p', [AdminResourceController::class, 'p2p'])->name('p2p');
    Route::post('/p2p/appeals/{id}/resolve', [AdminResourceController::class, 'resolveAppeal'])->name('p2p.appeals.resolve');
    Route::get('/adjustments', [AdminResourceController::class, 'adjustments'])->name('adjustments');
    Route::post('/adjustments', [AdminResourceController::class, 'requestAdjustment'])->name('adjustments.store');
    Route::post('/adjustments/{id}/approve', [AdminResourceController::class, 'approveAdjustment'])->name('adjustments.approve');
    Route::post('/news', [AdminResourceController::class, 'storeNews'])->name('news.store');
    Route::post('/blog', [AdminResourceController::class, 'storeBlog'])->name('blog.store');
    Route::post('/email/templates', [AdminResourceController::class, 'storeEmailTemplate'])->name('email.templates.store');
    Route::post('/email/campaigns', [AdminResourceController::class, 'storeCampaign'])->name('email.campaigns.store');
    Route::post('/email/send-test', [AdminResourceController::class, 'sendTestEmail'])->name('email.send-test');
    Route::get('/{module}', [AdminResourceController::class, 'module'])->name('module')
        ->where('module', 'copy-trading|ai-bots|mining|investments|stocks|forex|futures|metatrader|nft|virtual-cards|tax|news|blog|cms|fees|risk|compliance|audit-logs|support|settings|email|wallets|funding-notes|assets|swap');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/kyc-onboarding', fn () => redirect()->route('app.settings.kyc'))->middleware('auth')->name('kyc-onboarding');
Route::view('/two-factor', 'auth.two-factor')->middleware('auth')->name('two-factor');

require __DIR__.'/auth.php';
