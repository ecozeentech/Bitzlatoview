<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AiBot;
use App\Models\AiBotAllocation;
use App\Models\AiBotTrade;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\PricingService;
use App\Services\TransactionalMailService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiBotController extends Controller
{
    public function index()
    {
        return $this->marketplace();
    }

    public function marketplace()
    {
        $bots = AiBot::where('status', 'active')->get();

        return view('app.ai-bots.marketplace', compact('bots'));
    }

    public function myBots()
    {
        $allocations = AiBotAllocation::where('user_id', Auth::id())->with('bot', 'trades')->latest()->get();

        return view('app.ai-bots.my-bots', compact('allocations'));
    }

    public function show(AiBot $bot)
    {
        $myAllocation = AiBotAllocation::where('user_id', Auth::id())->where('ai_bot_id', $bot->id)->where('status', '!=', 'stopped')->first();

        return view('app.ai-bots.show', compact('bot', 'myAllocation'));
    }

    public function allocate(Request $request, AiBot $bot, LedgerService $ledger, PricingService $pricing, TransactionalMailService $mailer)
    {
        $user = Auth::user();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$bot->min_allocation],
        ]);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();

        try {
            $ledger->lockFunds($wallet, $usdt, (string) $data['amount']);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient available balance in your Investment Wallet.');
        }

        $allocation = AiBotAllocation::create([
            'user_id' => $user->id,
            'ai_bot_id' => $bot->id,
            'amount' => $data['amount'],
            'status' => 'active',
            'started_at' => now(),
            'unlocks_at' => $bot->lock_days > 0 ? now()->addDays($bot->lock_days) : null,
        ]);

        $trackedSymbol = $bot->supported_assets[0] ?? 'BTC';
        $trackedAsset = Asset::where('symbol', $trackedSymbol)->first();

        AiBotTrade::create([
            'ai_bot_allocation_id' => $allocation->id,
            'asset_symbol' => $trackedSymbol,
            'side' => 'long',
            'amount' => $data['amount'],
            'entry_price' => $trackedAsset ? $pricing->usdPrice($trackedAsset) : null,
            'pnl' => 0,
            'executed_at' => now(),
        ]);

        AuditLog::record($user, 'ai_bot.allocated', AiBotAllocation::class, $allocation->id);
        $mailer->send($user, 'bot_started', ['name' => $user->name, 'bot' => $bot->name, 'amount' => number_format((float) $data['amount'], 2)]);

        return back()->with('success', "Allocated \${$data['amount']} to {$bot->name}. AI trading bots are experimental — this strategy runs on Bitzlatoview's internal engine (not a live external exchange) and tracks real market prices for {$trackedSymbol}. You may lose money.");
    }

    public function pause(AiBotAllocation $allocation)
    {
        $this->authorizeOwner($allocation);
        $allocation->update(['status' => 'paused']);

        return back()->with('success', 'Bot allocation paused.');
    }

    public function resume(AiBotAllocation $allocation)
    {
        $this->authorizeOwner($allocation);
        $allocation->update(['status' => 'active']);

        return back()->with('success', 'Bot allocation resumed.');
    }

    public function stop(AiBotAllocation $allocation, LedgerService $ledger, PricingService $pricing)
    {
        $this->authorizeOwner($allocation);

        if ($allocation->unlocks_at && $allocation->unlocks_at->isFuture()) {
            return back()->with('error', 'This allocation is locked until '.$allocation->unlocks_at->format('M d, Y').'.');
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $allocation->user_id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_INVESTMENT);
        $bot = $allocation->bot;
        $trade = $allocation->trades()->whereNull('closed_at')->latest()->first();

        // Settle against real market price movement over the allocation's lifetime rather
        // than a random number. Exposure scales with the bot's disclosed risk score, capped
        // so a loss can never exceed the amount actually allocated (no leverage/debt here).
        $pnl = 0.0;
        $exitPrice = null;

        if ($trade && $trade->entry_price > 0) {
            $trackedAsset = Asset::where('symbol', $trade->asset_symbol)->first();
            $exitPrice = $trackedAsset ? $pricing->usdPrice($trackedAsset) : null;

            if ($exitPrice) {
                $priceChangePct = ($exitPrice - $trade->entry_price) / $trade->entry_price;
                $exposure = max($bot->risk_score, 1) / 50; // risk_score 50 => 1x exposure to the tracked asset
                $pnl = round($allocation->amount * $priceChangePct * $exposure, 2);
                $pnl = max($pnl, -1 * (float) $allocation->amount);
            }
        }

        $ledger->unlockFunds($wallet, $usdt, (string) $allocation->amount);

        if ($pnl > 0) {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $pnl],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $pnl],
                ],
                referenceType: 'ai_bot_pnl',
                referenceId: $allocation->id,
                description: 'AI bot gain settled on stop (based on real market price movement)',
            );
        } elseif ($pnl < 0) {
            $loss = min(abs($pnl), $allocation->amount);
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $loss],
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $loss],
                ],
                referenceType: 'ai_bot_pnl',
                referenceId: $allocation->id,
                description: 'AI bot loss settled on stop (based on real market price movement)',
            );
            $pnl = -$loss;
        }

        $trade?->update(['exit_price' => $exitPrice, 'pnl' => $pnl, 'closed_at' => now()]);
        $allocation->update(['status' => 'stopped', 'pnl' => $pnl, 'stopped_at' => now()]);

        AuditLog::record(Auth::user(), 'ai_bot.stopped', AiBotAllocation::class, $allocation->id);

        return back()->with('success', 'Bot stopped and funds released back to your Investment Wallet.');
    }

    protected function authorizeOwner(AiBotAllocation $allocation): void
    {
        abort_unless($allocation->user_id === Auth::id(), 403);
    }
}
