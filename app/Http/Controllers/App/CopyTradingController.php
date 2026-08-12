<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\CopiedTrade;
use App\Models\CopyAllocation;
use App\Models\SystemSetting;
use App\Models\TraderProfile;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\PricingService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CopyTradingController extends Controller
{
    /** Categories with a live CoinGecko-backed price we can settle real P&L against. */
    protected const LIVE_PRICED_CATEGORIES = ['crypto', 'futures'];

    /** SystemSetting key admins use to configure the platform-wide floor (see /admin/settings). */
    protected const MIN_AMOUNT_SETTING_KEY = 'copy_trading.min_amount';

    public static function globalMinimumAmount(): float
    {
        return (float) SystemSetting::getValue(self::MIN_AMOUNT_SETTING_KEY, 100);
    }

    public static function minAmountSettingKey(): string
    {
        return self::MIN_AMOUNT_SETTING_KEY;
    }

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
        $globalMinAmount = self::globalMinimumAmount();

        return view('app.copy-trading.show', compact('trader', 'myAllocation', 'globalMinAmount'));
    }

    public function myCopies()
    {
        $allocations = CopyAllocation::where('user_id', Auth::id())->with('trader', 'trades')->latest()->get();

        return view('app.copy-trading.my-copies', compact('allocations'));
    }

    public function allocate(Request $request, TraderProfile $trader, LedgerService $ledger, PricingService $pricing)
    {
        $user = Auth::user();
        $globalMinAmount = self::globalMinimumAmount();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'minimum_amount' => ['required', 'numeric', 'min:'.$globalMinAmount],
            'stop_loss_pct' => ['nullable', 'numeric', 'min:1', 'max:90'],
            'take_profit_pct' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'max_position_size' => ['nullable', 'numeric', 'gt:0'],
            'copy_ratio' => ['nullable', 'numeric', 'min:0.1', 'max:5'],
        ], [
            'minimum_amount.min' => "The minimum investment amount must be at least \${$globalMinAmount} (the platform-wide floor set by the admin).",
        ]);

        if ((float) $data['amount'] < (float) $data['minimum_amount']) {
            return back()->withInput()->with('error', 'Your allocation amount must be at least your chosen minimum investment amount.');
        }

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

        $btc = Asset::where('symbol', 'BTC')->first();
        $entryPrice = in_array($trader->category, self::LIVE_PRICED_CATEGORIES) && $btc
            ? $pricing->usdPrice($btc)
            : 1;

        CopiedTrade::create([
            'copy_allocation_id' => $allocation->id,
            'asset_symbol' => in_array($trader->category, self::LIVE_PRICED_CATEGORIES) ? 'BTC/USDT' : strtoupper($trader->category),
            'side' => 'long',
            'entry_price' => $entryPrice,
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

    public function stop(CopyAllocation $allocation, LedgerService $ledger, PricingService $pricing)
    {
        $this->authorizeOwner($allocation);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $allocation->user_id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_INVESTMENT);

        $trader = $allocation->trader;
        $openTrade = $allocation->trades()->whereNull('closed_at')->latest()->first();
        $exitPrice = null;
        $pnl = 0.0;

        if (in_array($trader->category, self::LIVE_PRICED_CATEGORIES) && $openTrade && $openTrade->entry_price > 0) {
            // Settle against BTC's real price movement over the holding period, scaled by
            // the user's copy ratio — an honest proxy until each trader is running real,
            // independently verifiable trades on Bitzlatoview's own order book.
            $btc = Asset::where('symbol', 'BTC')->first();
            $exitPrice = $btc ? $pricing->usdPrice($btc) : null;

            if ($exitPrice) {
                $priceChangePct = ($exitPrice - $openTrade->entry_price) / $openTrade->entry_price;
                $pnl = round($allocation->amount * $priceChangePct * (float) $allocation->copy_ratio, 2);
                $pnl = max($pnl, -1 * (float) $allocation->amount);
            }
        } else {
            // No live market feed for this trader's category yet (forex/stocks/P2P). Prorate
            // the trader's disclosed, published return over the time actually held, rather
            // than generating an unrelated random number.
            $daysHeld = max($allocation->created_at->diffInDays(now()), 1);
            $returnPct = ($trader->return_30d_pct / 100) * min($daysHeld / 30, 3);
            $pnl = round($allocation->amount * $returnPct * (float) $allocation->copy_ratio, 2);
            $pnl = max($pnl, -1 * (float) $allocation->amount);
        }

        $ledger->unlockFunds($wallet, $usdt, (string) $allocation->amount);

        if ($pnl > 0) {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $pnl],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $pnl],
                ],
                referenceType: 'copy_trading_pnl',
                referenceId: $allocation->id,
                description: 'Copy trading gain settled on stop',
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
                description: 'Copy trading loss settled on stop',
            );
            $pnl = -$loss;
        }

        $openTrade?->update(['exit_price' => $exitPrice ?? $openTrade->entry_price, 'closed_at' => now(), 'pnl' => $pnl]);
        $allocation->update(['status' => 'stopped', 'pnl' => $pnl]);

        AuditLog::record(Auth::user(), 'copy_trading.stopped', CopyAllocation::class, $allocation->id);

        return back()->with('success', 'Copy allocation stopped and funds released back to your Investment Wallet.');
    }

    protected function authorizeOwner(CopyAllocation $allocation): void
    {
        abort_unless($allocation->user_id === Auth::id(), 403);
    }
}
