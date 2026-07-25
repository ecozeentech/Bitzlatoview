<?php

use App\Http\Controllers\Api\PlatformApiController;
use Illuminate\Support\Facades\Route;

/*
| Public market/content APIs (simulation mode).
| Authenticated JSON endpoints are available via web session on /api/* when called from the app,
| or connect Laravel Sanctum / Passport for production token auth.
*/

Route::get('/markets', [PlatformApiController::class, 'markets']);
Route::get('/markets/{symbol}', [PlatformApiController::class, 'marketShow']);
Route::get('/top-gainers', [PlatformApiController::class, 'topGainers']);
Route::get('/top-losers', [PlatformApiController::class, 'topLosers']);
Route::get('/news', [PlatformApiController::class, 'news']);
Route::get('/blog', [PlatformApiController::class, 'blog']);
Route::get('/blog/{slug}', [PlatformApiController::class, 'blogShow']);
Route::get('/p2p/ads', [PlatformApiController::class, 'p2pAds']);
Route::get('/copy-trading/traders', [PlatformApiController::class, 'copyTraders']);
Route::get('/ai-bots', [PlatformApiController::class, 'aiBots']);
Route::get('/mining/packages', [PlatformApiController::class, 'miningPackages']);
Route::get('/swap/quote', [PlatformApiController::class, 'swapQuote']);

Route::middleware('auth')->group(function () {
    Route::get('/me', [PlatformApiController::class, 'me']);
    Route::get('/wallets', [PlatformApiController::class, 'wallets']);
    Route::get('/swap/history', [PlatformApiController::class, 'swapHistory']);
    Route::get('/orders', [PlatformApiController::class, 'orders']);
    Route::post('/orders', [PlatformApiController::class, 'placeOrder']);
    Route::get('/trades', [PlatformApiController::class, 'trades']);
    Route::get('/p2p/orders', [PlatformApiController::class, 'p2pOrders']);
    Route::get('/cards', [PlatformApiController::class, 'cards']);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [PlatformApiController::class, 'adminDashboard']);
});
