<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\CopiedTrade;
use App\Models\CopyAllocation;
use App\Models\TraderProfile;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CopyTradingController extends Controller
{
    public function index(Request $request)
    {
        return $this->traders($request);
    }

    public function traders(Request $request)
    {
        $category = $request->query('category', 'all');

        $traders = TraderProfile::where('status', 'active')
            ->when($category !== 'all', fn ($q) => $q->where('category', $category))
            ->orderByDesc('is_featured')->orderByDesc('return_30d_pct')->get();

        return view('app.copy-trading.traders', compact('traders', 'category'));
    }

    public function show(TraderProfile $trader)
    {
        $trader->load('snapshots');
        $myAllocation = CopyAllocation::where('user_id', Auth::id())->where('trader_profile_id', $trader->id)->where('status', '!=', 'stopped')->first();

        return view('app.copy-trading.show', compact('trader', 'myAllocation'));
    }

    public function myCopies()
    {
        $allocations = CopyAllocation::where('user_id', Auth::id())->with('trader', 'trades')->latest()->get();

        return view('app.copy-trading.my-copies', compact('allocations'));
    }

    public function allocate(Request $request, TraderProfile $trader, LedgerService $ledger)
    {
        $user = Auth::user();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'stop_loss_pct' => ['nullable', 'numeric', 'min:1', 'max:90'],
            'take_profit_pct' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'max_position_size' => ['nullable', 'numeric', 'gt:0'],
            'copy_ratio' => ['nullable', 'numeric', 'min:0.1', 'max:5'],
        ]);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();

        try {
            $ledger->lockFunds($wallet, $usdt, (string) $data['amount']);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient available balance in your Investment Wallet.');
        }

        $allocation = CopyAllocation::create($data + [
            'user_id' => $user->id,
            'trader_profile_id' => $trader->id,
            'copy_ratio' => $data['copy_ratio'] ?? 1,
            'status' => 'active',
        ]);

        $trader->increment('followers_count');

        CopiedTrade::create([
            'copy_allocation_id' => $allocation->id,
            'asset_symbol' => 'BTC/USDT',
            'side' => 'long',
            'entry_price' => 0,
            'opened_at' => now(),
        ]);

        AuditLog::record($user, 'copy_trading.allocated', CopyAllocation::class, $allocation->id);

        return back()->with('success', "Allocated \${$data['amount']} to follow {$trader->display_name}.");
    }

    public function pause(CopyAllocation $allocation)
    {
        $this->authorizeOwner($allocation);
        $allocation->update(['status' => 'paused']);

        return back()->with('success', 'Copy allocation paused.');
    }

    public function resume(CopyAllocation $allocation)
    {
        $this->authorizeOwner($allocation);
        $allocation->update(['status' => 'active']);

        return back()->with('success', 'Copy allocation resumed.');
    }

    public function stop(CopyAllocation $allocation, LedgerService $ledger)
    {
        $this->authorizeOwner($allocation);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $allocation->user_id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_INVESTMENT);

        $trader = $allocation->trader;
        $simulatedReturnPct = ($trader->return_30d_pct / 100) * (mt_rand(50, 150) / 100);
        $pnl = round($allocation->amount * $simulatedReturnPct, 2);

        $ledger->unlockFunds($wallet, $usdt, (string) $allocation->amount);

        if ($pnl > 0) {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $pnl],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $pnl],
                ],
                referenceType: 'copy_trading_pnl',
                referenceId: $allocation->id,
                description: 'Simulated copy trading gain on stop',
            );
        } elseif ($pnl < 0) {
            $loss = min(abs($pnl), $allocation->amount);
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $loss],
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $loss],
                ],
                referenceType: 'copy_trading_pnl',
                referenceId: $allocation->id,
                description: 'Simulated copy trading loss on stop',
            );
            $pnl = -$loss;
        }

        $allocation->trades()->latest()->first()?->update(['exit_price' => 1, 'closed_at' => now(), 'pnl' => $pnl]);
        $allocation->update(['status' => 'stopped', 'pnl' => $pnl]);

        AuditLog::record(Auth::user(), 'copy_trading.stopped', CopyAllocation::class, $allocation->id);

        return back()->with('success', 'Copy allocation stopped and funds released back to your Investment Wallet.');
    }

    protected function authorizeOwner(CopyAllocation $allocation): void
    {
        abort_unless($allocation->user_id === Auth::id(), 403);
    }
}
