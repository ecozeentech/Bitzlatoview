<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AiBotAllocation;
use App\Models\Asset;
use App\Models\CopyAllocation;
use App\Models\FuturesPosition;
use App\Models\MarketPair;
use App\Models\MiningContract;
use App\Models\NewsArticle;
use App\Models\NftCollection;
use App\Models\Order;
use App\Models\StockInstrument;
use App\Models\TraderProfile;
use App\Models\VirtualCard;
use App\Models\WatchlistItem;
use App\Services\PricingService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(PricingService $pricing)
    {
        $user = Auth::user();

        $wallets = $user->walletAccounts()->with('balances.asset')->get()->keyBy('type');

        $walletTotals = [];
        $portfolioTotal = 0.0;

        foreach ($wallets as $type => $wallet) {
            $total = 0.0;
            foreach ($wallet->balances as $balance) {
                $total += ((float) $balance->available + (float) $balance->locked) * $pricing->usdPrice($balance->asset);
            }
            $walletTotals[$type] = $total;
            $portfolioTotal += $total;
        }

        $openOrders = Order::where('user_id', $user->id)->whereIn('status', ['new', 'partially_filled'])->count();
        $openFutures = FuturesPosition::where('user_id', $user->id)->where('status', 'open')->count();
        $activeBots = AiBotAllocation::where('user_id', $user->id)->where('status', 'active')->count();
        $miningActive = MiningContract::where('user_id', $user->id)->where('status', 'active')->count();
        $copyPnl = CopyAllocation::where('user_id', $user->id)->sum('pnl');
        $cardSpend = VirtualCard::where('user_id', $user->id)->withSum('transactions', 'amount')->get()->sum('transactions_sum_amount');

        $watchlist = WatchlistItem::where('user_id', $user->id)->with('marketPair.baseAsset', 'marketPair.quote')->take(6)->get();

        $markets = MarketPair::where('is_active', true)->with(['baseAsset', 'quote'])->get();
        $topGainers = $markets->sortByDesc(fn ($m) => $m->quote?->change_24h_pct)->take(5);
        $movers = $markets->sortByDesc(fn ($m) => abs($m->quote?->change_24h_pct ?? 0))->take(8)->values();

        $news = NewsArticle::latest('published_at')->take(4)->get();

        $nftCollections = NftCollection::withCount('items')->orderByDesc('volume')->take(6)->get();
        $stockInstruments = StockInstrument::where('is_active', true)->take(6)->get();

        $topTraders = TraderProfile::where('status', 'active')
            ->orderByDesc('is_featured')->orderByDesc('return_30d_pct')->take(6)->get();

        $btcAsset = Asset::where('symbol', 'BTC')->first();
        $btcPrice = $btcAsset ? $pricing->usdPrice($btcAsset) : 0;

        return view('app.dashboard', [
            'wallets' => $wallets,
            'walletTotals' => $walletTotals,
            'portfolioTotal' => $portfolioTotal,
            'portfolioTotalBtc' => $btcPrice > 0 ? $portfolioTotal / $btcPrice : 0,
            'openOrders' => $openOrders,
            'openFutures' => $openFutures,
            'activeBots' => $activeBots,
            'miningActive' => $miningActive,
            'copyPnl' => $copyPnl,
            'cardSpend' => $cardSpend,
            'watchlist' => $watchlist,
            'markets' => $markets,
            'topGainers' => $topGainers,
            'movers' => $movers,
            'news' => $news,
            'nftCollections' => $nftCollections,
            'stockInstruments' => $stockInstruments,
            'topTraders' => $topTraders,
        ]);
    }

    public function markNotificationsRead()
    {
        session(['notifications_last_seen_at' => now()]);

        \App\Models\AdminMessage::where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);

        return response()->noContent();
    }
}
