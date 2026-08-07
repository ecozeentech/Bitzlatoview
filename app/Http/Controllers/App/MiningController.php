<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\MiningContract;
use App\Models\MiningPackage;
use App\Models\MiningReward;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\RewardAccrualService;
use App\Services\TransactionalMailService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MiningController extends Controller
{
    public function index(RewardAccrualService $accrual)
    {
        $accrual->accrueMining(Auth::user());

        $packages = MiningPackage::where('is_published', true)->with('asset')->get();
        $contracts = MiningContract::where('user_id', Auth::id())->with('package.asset')->latest()->get();

        return view('app.mining.index', compact('packages', 'contracts'));
    }

    public function contracts(RewardAccrualService $accrual)
    {
        $accrual->accrueMining(Auth::user());
        $contracts = MiningContract::where('user_id', Auth::id())->with('package.asset', 'rewards')->latest()->get();

        return view('app.mining.contracts', compact('contracts'));
    }

    public function rewards(RewardAccrualService $accrual)
    {
        $accrual->accrueMining(Auth::user());
        $rewards = MiningReward::whereHas('contract', fn ($q) => $q->where('user_id', Auth::id()))
            ->with('contract.package.asset')->latest('credited_at')->take(50)->get();

        return view('app.mining.rewards', compact('rewards'));
    }

    public function purchase(Request $request, MiningPackage $package, LedgerService $ledger, TransactionalMailService $mailer)
    {
        $user = Auth::user();

        $data = $request->validate([
            'multiplier' => ['nullable', 'integer', 'min:1', 'max:10'],
            'reward_destination' => ['required', 'in:primary,investment'],
        ]);

        $multiplier = $data['multiplier'] ?? 1;
        $cost = $package->price * $multiplier;

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_INVESTMENT);

        try {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $cost],
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $cost],
                ],
                referenceType: 'mining_contract_purchase',
                description: "Purchased {$package->name} x{$multiplier}",
                createdBy: $user,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient available balance in your Investment Wallet.');
        }

        $contract = MiningContract::create([
            'user_id' => $user->id,
            'mining_package_id' => $package->id,
            'amount_invested' => $cost,
            'reward_destination' => $data['reward_destination'],
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays($package->term_days)->toDateString(),
            'status' => 'active',
        ]);

        AuditLog::record($user, 'mining.purchased', MiningContract::class, $contract->id);
        $mailer->send($user, 'mining_contract_purchased', ['name' => $user->name, 'package' => $package->name, 'amount' => number_format($cost, 2)]);

        return redirect()->route('app.mining.contracts')->with('success', "Purchased {$package->name}. Rewards will accrue daily to your ".ucfirst($data['reward_destination']).' Wallet.');
    }
}
