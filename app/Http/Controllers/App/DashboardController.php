<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AiBotAllocation;
use App\Models\CopyAllocation;
use App\Models\FuturesPosition;
use App\Models\MarketPair;
use App\Models\MiningContract;
use App\Models\MiningReward;
use App\Models\NewsArticle;
use App\Models\Order;
use App\Models\TaxReport;
use App\Models\VirtualCard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $primary = $user->walletAccount('PRIMARY');
        $trading = $user->walletAccount('TRADING');
        $investment = $user->walletAccount('INVESTMENT');

        $estimate = function ($wallet) {
            if (! $wallet) {
                return '0.00';
            }
            $total = '0';
            foreach ($wallet->balances()->with('asset')->get() as $balance) {
                $price = (string) ($balance->asset->mock_price_usd ?: 0);
                $qty = bcadd((string) $balance->available, (string) $balance->locked, 8);
                $total = bcadd($total, bcmul($qty, $price, 8), 8);
            }

            return $total;
        };

        $primaryUsd = $estimate($primary);
        $tradingUsd = $estimate($trading);
        $investmentUsd = $estimate($investment);
        $portfolio = bcadd(bcadd($primaryUsd, $tradingUsd, 8), $investmentUsd, 8);

        return view('app.dashboard', [
            'user' => $user,
            'portfolio' => $portfolio,
            'primaryUsd' => $primaryUsd,
            'tradingUsd' => $tradingUsd,
            'investmentUsd' => $investmentUsd,
            'openOrders' => Order::query()->where('user_id', $user->id)->whereIn('status', ['new', 'partially_filled'])->count(),
            'futuresPositions' => FuturesPosition::query()->where('user_id', $user->id)->where('status', 'open')->count(),
            'botAllocations' => AiBotAllocation::query()->where('user_id', $user->id)->where('status', 'active')->count(),
            'miningRewards' => MiningReward::query()->whereHas('miningContract', fn ($q) => $q->where('user_id', $user->id))->sum('amount'),
            'copyPnl' => CopyAllocation::query()->where('user_id', $user->id)->sum('pnl'),
            'cardSpend' => VirtualCard::query()->where('user_id', $user->id)->sum('spent_amount'),
            'taxReport' => TaxReport::query()->where('user_id', $user->id)->where('tax_year', now()->year)->first(),
            'gainers' => MarketPair::query()->where('is_active', true)->orderByDesc('change_24h')->limit(5)->get(),
            'news' => NewsArticle::query()->where('status', 'published')->latest('published_at')->limit(4)->get(),
            'activeMining' => MiningContract::query()->where('user_id', $user->id)->where('status', 'active')->count(),
        ]);
    }
}
