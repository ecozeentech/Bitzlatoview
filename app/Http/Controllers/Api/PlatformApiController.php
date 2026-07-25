<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiBot;
use App\Models\Asset;
use App\Models\BlogPost;
use App\Models\CopyTraderProfile;
use App\Models\MarketPair;
use App\Models\MiningPackage;
use App\Models\NewsArticle;
use App\Models\Order;
use App\Models\P2PAd;
use App\Models\P2POrder;
use App\Models\SwapTransaction;
use App\Models\Trade;
use App\Models\VirtualCard;
use App\Services\SwapService;
use App\Services\TradingSimulationService;
use Illuminate\Http\Request;

class PlatformApiController extends Controller
{
    public function me(Request $request)
    {
        return response()->json($request->user()->load('profile', 'walletAccounts.balances.asset'));
    }

    public function markets()
    {
        return MarketPair::query()->where('is_active', true)->orderByDesc('volume_24h')->get();
    }

    public function marketShow(string $symbol)
    {
        return MarketPair::query()->where('symbol', strtoupper($symbol))->firstOrFail();
    }

    public function topGainers()
    {
        return MarketPair::query()->where('is_active', true)->orderByDesc('change_24h')->limit(20)->get();
    }

    public function topLosers()
    {
        return MarketPair::query()->where('is_active', true)->orderBy('change_24h')->limit(20)->get();
    }

    public function wallets(Request $request)
    {
        return $request->user()->walletAccounts()->with('balances.asset')->get();
    }

    public function swapQuote(Request $request, SwapService $swap)
    {
        $data = $request->validate([
            'from_asset_id' => ['required', 'exists:assets,id'],
            'to_asset_id' => ['required', 'exists:assets,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        return $swap->quote(
            Asset::query()->findOrFail($data['from_asset_id']),
            Asset::query()->findOrFail($data['to_asset_id']),
            $data['amount']
        );
    }

    public function swapHistory(Request $request)
    {
        return SwapTransaction::query()->where('user_id', $request->user()->id)->latest()->paginate(50);
    }

    public function orders(Request $request)
    {
        return Order::query()->where('user_id', $request->user()->id)->latest()->paginate(50);
    }

    public function trades(Request $request)
    {
        return Trade::query()->where('user_id', $request->user()->id)->latest()->paginate(50);
    }

    public function placeOrder(Request $request, TradingSimulationService $trading)
    {
        $data = $request->validate([
            'symbol' => ['required', 'string'],
            'side' => ['required', 'in:buy,sell'],
            'type' => ['required', 'in:market,limit'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'price' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $pair = MarketPair::query()->where('symbol', strtoupper($data['symbol']))->firstOrFail();

        return $trading->placeSpotOrder($request->user(), $pair, $data);
    }

    public function p2pAds()
    {
        return P2PAd::query()->where('status', 'active')->where('is_visible', true)->with('asset')->latest()->paginate(50);
    }

    public function p2pOrders(Request $request)
    {
        $uid = $request->user()->id;

        return P2POrder::query()->where(fn ($q) => $q->where('buyer_id', $uid)->orWhere('seller_id', $uid))->latest()->paginate(50);
    }

    public function copyTraders()
    {
        return CopyTraderProfile::query()->where('status', 'active')->orderByDesc('followers')->get();
    }

    public function aiBots()
    {
        return AiBot::query()->where('is_active', true)->get();
    }

    public function miningPackages()
    {
        return MiningPackage::query()->where('is_published', true)->get();
    }

    public function cards(Request $request)
    {
        return VirtualCard::query()->where('user_id', $request->user()->id)->get();
    }

    public function news()
    {
        return NewsArticle::query()->where('status', 'published')->latest('published_at')->paginate(20);
    }

    public function blog()
    {
        return BlogPost::query()->where('status', 'published')->latest('published_at')->paginate(20);
    }

    public function blogShow(string $slug)
    {
        return BlogPost::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();
    }

    public function adminDashboard()
    {
        return [
            'users' => \App\Models\User::query()->count(),
            'kyc_pending' => \App\Models\KycSubmission::query()->whereIn('status', ['submitted', 'under_review'])->count(),
            'pending_withdrawals' => \App\Models\Withdrawal::query()->where('status', 'pending_review')->count(),
            'open_appeals' => \App\Models\P2PAppeal::query()->where('status', 'open')->count(),
        ];
    }
}
