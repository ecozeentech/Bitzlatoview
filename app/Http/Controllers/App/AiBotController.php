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

    public function allocate(Request $request, AiBot $bot, LedgerService $ledger)
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

        AiBotTrade::create([
            'ai_bot_allocation_id' => $allocation->id,
            'asset_symbol' => $bot->supported_assets[0] ?? 'BTC',
            'side' => 'long',
            'amount' => $data['amount'],
            'pnl' => 0,
            'executed_at' => now(),
        ]);

        AuditLog::record($user, 'ai_bot.allocated', AiBotAllocation::class, $allocation->id);

        return back()->with('success', "Allocated \${$data['amount']} to {$bot->name}. AI trading bots are experimental and may lose money.");
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

    public function stop(AiBotAllocation $allocation, LedgerService $ledger)
    {
        $this->authorizeOwner($allocation);

        if ($allocation->unlocks_at && $allocation->unlocks_at->isFuture()) {
            return back()->with('error', 'This allocation is locked until '.$allocation->unlocks_at->format('M d, Y').'.');
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $allocation->user_id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_INVESTMENT);
        $bot = $allocation->bot;

        $simulatedReturnPct = ($bot->historical_return_pct / 100) * (mt_rand(40, 160) / 100);
        $pnl = round($allocation->amount * $simulatedReturnPct, 2);

        $ledger->unlockFunds($wallet, $usdt, (string) $allocation->amount);

        if ($pnl > 0) {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $pnl],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $pnl],
                ],
                referenceType: 'ai_bot_pnl',
                referenceId: $allocation->id,
                description: 'Simulated AI bot gain on stop',
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
                description: 'Simulated AI bot loss on stop',
            );
            $pnl = -$loss;
        }

        $allocation->update(['status' => 'stopped', 'pnl' => $pnl, 'stopped_at' => now()]);

        AuditLog::record(Auth::user(), 'ai_bot.stopped', AiBotAllocation::class, $allocation->id);

        return back()->with('success', 'Bot stopped and funds released back to your Investment Wallet.');
    }

    protected function authorizeOwner(AiBotAllocation $allocation): void
    {
        abort_unless($allocation->user_id === Auth::id(), 403);
    }
}
