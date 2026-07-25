<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AiBot;
use App\Models\AiBotAllocation;
use App\Models\Asset;
use App\Models\CopyAllocation;
use App\Models\CopyTraderProfile;
use App\Models\InvestmentProduct;
use App\Models\InvestmentSubscription;
use App\Models\MiningContract;
use App\Models\MiningPackage;
use App\Models\MiningReward;
use App\Services\LedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvestController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

    public function copyTrading(): View
    {
        return view('app.copy-trading.index', [
            'traders' => CopyTraderProfile::query()->where('status', 'active')->orderByDesc('followers')->get(),
        ]);
    }

    public function traders(Request $request): View
    {
        $category = $request->get('category', 'crypto');

        return view('app.copy-trading.traders', [
            'category' => $category,
            'traders' => CopyTraderProfile::query()
                ->where('status', 'active')
                ->when($category !== 'all', fn ($q) => $q->where('category', $category))
                ->orderByDesc('followers')
                ->get(),
        ]);
    }

    public function myCopies(Request $request): View
    {
        return view('app.copy-trading.my-copies', [
            'allocations' => CopyAllocation::query()->where('user_id', $request->user()->id)->with('copyTraderProfile')->latest()->get(),
        ]);
    }

    public function allocateCopy(Request $request, int $traderId): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'stop_loss' => ['nullable', 'numeric'],
            'take_profit' => ['nullable', 'numeric'],
            'copy_ratio' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $user = $request->user();
        $wallet = $user->walletAccount('INVESTMENT');
        $usdt = Asset::query()->where('symbol', 'USDT')->firstOrFail();
        $trader = CopyTraderProfile::query()->findOrFail($traderId);

        $allocation = CopyAllocation::query()->create([
            'user_id' => $user->id,
            'copy_trader_profile_id' => $trader->id,
            'wallet_account_id' => $wallet->id,
            'asset_id' => $usdt->id,
            'allocation_amount' => $data['amount'],
            'copy_ratio' => $data['copy_ratio'] ?? 1,
            'stop_loss' => $data['stop_loss'] ?? null,
            'take_profit' => $data['take_profit'] ?? null,
            'status' => 'active',
            'is_simulated' => true,
        ]);

        $this->ledger->lockFunds(
            $wallet,
            $usdt,
            $data['amount'],
            'copy_allocate',
            'copy-'.$allocation->id.'-'.Str::random(6),
            CopyAllocation::class,
            $allocation->id,
            'Copy trading allocation lock'
        );

        return back()->with('success', 'Copy allocation started (simulated). No returns are guaranteed.');
    }

    public function pauseCopy(Request $request, int $id): RedirectResponse
    {
        $allocation = CopyAllocation::query()->where('user_id', $request->user()->id)->findOrFail($id);
        $allocation->update(['status' => 'paused']);

        return back()->with('success', 'Copy paused.');
    }

    public function stopCopy(Request $request, int $id): RedirectResponse
    {
        $allocation = CopyAllocation::query()->where('user_id', $request->user()->id)->findOrFail($id);
        $wallet = $request->user()->walletAccount('INVESTMENT');
        $asset = Asset::query()->findOrFail($allocation->asset_id);

        $this->ledger->unlockFunds(
            $wallet,
            $asset,
            (string) $allocation->allocation_amount,
            'copy_stop',
            'copy-stop-'.$allocation->id,
            CopyAllocation::class,
            $allocation->id,
            'Stop copy unlock'
        );
        $allocation->update(['status' => 'stopped']);

        return back()->with('success', 'Copy stopped and funds unlocked.');
    }

    public function aiBots(): View
    {
        return view('app.ai-bots.index', [
            'bots' => AiBot::query()->where('is_active', true)->get(),
        ]);
    }

    public function aiMarketplace(): View
    {
        return view('app.ai-bots.marketplace', [
            'bots' => AiBot::query()->where('is_active', true)->get(),
        ]);
    }

    public function myBots(Request $request): View
    {
        return view('app.ai-bots.my-bots', [
            'allocations' => AiBotAllocation::query()->where('user_id', $request->user()->id)->with('aiBot')->latest()->get(),
        ]);
    }

    public function allocateBot(Request $request, int $botId): RedirectResponse
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0']]);
        $bot = AiBot::query()->findOrFail($botId);
        if (bccomp((string) $data['amount'], (string) $bot->min_allocation, 8) < 0) {
            return back()->with('error', 'Below minimum allocation.');
        }

        $user = $request->user();
        $wallet = $user->walletAccount('INVESTMENT');
        $usdt = Asset::query()->where('symbol', 'USDT')->firstOrFail();

        $allocation = AiBotAllocation::query()->create([
            'user_id' => $user->id,
            'ai_bot_id' => $bot->id,
            'wallet_account_id' => $wallet->id,
            'asset_id' => $usdt->id,
            'amount' => $data['amount'],
            'status' => 'active',
            'lock_until' => now()->addDays(7),
            'is_simulated' => true,
        ]);

        $this->ledger->lockFunds(
            $wallet,
            $usdt,
            $data['amount'],
            'bot_allocate',
            'bot-'.$allocation->id,
            AiBotAllocation::class,
            $allocation->id,
            'AI bot allocation'
        );

        return back()->with('success', 'Bot started in paper mode. AI bots are experimental and may lose money.');
    }

    public function pauseBot(Request $request, int $id): RedirectResponse
    {
        AiBotAllocation::query()->where('user_id', $request->user()->id)->findOrFail($id)->update(['status' => 'paused']);

        return back()->with('success', 'Bot paused.');
    }

    public function stopBot(Request $request, int $id): RedirectResponse
    {
        $allocation = AiBotAllocation::query()->where('user_id', $request->user()->id)->findOrFail($id);
        if ($allocation->lock_until && $allocation->lock_until->isFuture()) {
            return back()->with('error', 'Funds are still in lock period.');
        }

        $wallet = $request->user()->walletAccount('INVESTMENT');
        $asset = Asset::query()->findOrFail($allocation->asset_id);
        $this->ledger->unlockFunds($wallet, $asset, (string) $allocation->amount, 'bot_stop', 'bot-stop-'.$allocation->id, AiBotAllocation::class, $allocation->id);
        $allocation->update(['status' => 'stopped']);

        return back()->with('success', 'Bot stopped.');
    }

    public function mining(): View
    {
        return view('app.mining.index', [
            'packages' => MiningPackage::query()->where('is_published', true)->with('asset')->get(),
        ]);
    }

    public function miningContracts(Request $request): View
    {
        return view('app.mining.contracts', [
            'contracts' => MiningContract::query()->where('user_id', $request->user()->id)->with('miningPackage')->latest()->get(),
        ]);
    }

    public function miningRewards(Request $request): View
    {
        return view('app.mining.rewards', [
            'rewards' => MiningReward::query()->whereHas('miningContract', fn ($q) => $q->where('user_id', $request->user()->id))->latest()->get(),
        ]);
    }

    public function purchaseMining(Request $request, int $packageId): RedirectResponse
    {
        $package = MiningPackage::query()->findOrFail($packageId);
        $user = $request->user();
        $wallet = $user->walletAccount('INVESTMENT');
        $priceAsset = Asset::query()->findOrFail($package->price_asset_id);

        $contract = MiningContract::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'mining_package_id' => $package->id,
            'wallet_account_id' => $wallet->id,
            'hashrate' => $package->hashrate,
            'purchase_amount' => $package->price,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays($package->term_days),
            'reward_wallet' => 'INVESTMENT',
            'is_simulated' => true,
        ]);

        $this->ledger->debitAvailable(
            $wallet,
            $priceAsset,
            (string) $package->price,
            'mining_purchase',
            'mining-'.$contract->uuid,
            MiningContract::class,
            $contract->id,
            'Mining contract purchase'
        );

        return back()->with('success', 'Mining contract purchased (simulated rewards). Returns are not guaranteed.');
    }

    public function investments(): View
    {
        return view('app.investments.index', [
            'products' => InvestmentProduct::query()->where('is_active', true)->get(),
        ]);
    }

    public function subscribeInvestment(Request $request, int $productId): RedirectResponse
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0']]);
        $product = InvestmentProduct::query()->findOrFail($productId);
        $user = $request->user();
        $wallet = $user->walletAccount('INVESTMENT');
        $asset = Asset::query()->findOrFail($product->asset_id);

        $sub = InvestmentSubscription::query()->create([
            'user_id' => $user->id,
            'investment_product_id' => $product->id,
            'wallet_account_id' => $wallet->id,
            'amount' => $data['amount'],
            'status' => 'active',
            'lock_until' => now()->addDays($product->lock_days),
            'is_simulated' => true,
        ]);

        $this->ledger->lockFunds($wallet, $asset, $data['amount'], 'investment', 'invest-'.$sub->id, InvestmentSubscription::class, $sub->id);

        return back()->with('success', 'Investment subscribed (paper mode).');
    }
}
