<?php

namespace App\Services;

use App\Models\InvestmentReward;
use App\Models\InvestmentSubscription;
use App\Models\MiningContract;
use App\Models\MiningReward;
use App\Models\User;
use App\Models\WalletAccount;
use App\Support\House;
use Illuminate\Support\Carbon;

/**
 * Computes real, deterministic reward accrual for mining contracts and investment
 * subscriptions based on each product's disclosed reward rate. Rewards are computed lazily
 * (catch-up since the last credited timestamp) whenever a user views the relevant page,
 * rather than requiring a scheduled job — the math and ledger postings are real either way.
 */
class RewardAccrualService
{
    public function __construct(protected LedgerService $ledger) {}

    public function accrueMining(User $user): void
    {
        $contracts = MiningContract::where('user_id', $user->id)->where('status', 'active')->with('package.asset', 'rewards')->get();

        foreach ($contracts as $contract) {
            $this->accrueMiningContract($contract);
        }
    }

    protected function accrueMiningContract(MiningContract $contract): void
    {
        $package = $contract->package;
        $asset = $package->asset;
        $lastCredited = $contract->rewards()->max('credited_at');
        $since = $lastCredited ? Carbon::parse($lastCredited) : Carbon::parse($contract->start_date);
        $end = Carbon::parse($contract->end_date)->min(now());

        $days = (int) $since->diffInDays($end);
        if ($days < 1) {
            return;
        }

        $destinationType = $contract->reward_destination === 'primary' ? WalletAccount::TYPE_PRIMARY : WalletAccount::TYPE_INVESTMENT;
        $wallet = WalletAccount::firstOrCreate(['user_id' => $contract->user_id, 'type' => $destinationType]);
        $house = House::wallet($destinationType);

        $dailyReward = (float) $contract->amount_invested * ((float) $package->estimated_daily_reward_pct / 100);
        $maintenanceFee = $dailyReward * ((float) $package->maintenance_fee_pct / 100);
        $netDaily = max($dailyReward - $maintenanceFee, 0);

        for ($i = 1; $i <= $days; $i++) {
            $creditedAt = $since->copy()->addDays($i);
            $amount = round($netDaily, 8);
            if ($amount <= 0) {
                continue;
            }

            $this->ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
                ],
                referenceType: 'mining_reward',
                referenceId: $contract->id,
                description: "Mining reward for contract #{$contract->id}",
                idempotencyKey: "mining-reward-{$contract->id}-".$creditedAt->format('Y-m-d'),
            );

            MiningReward::create([
                'mining_contract_id' => $contract->id,
                'amount' => $amount,
                'credited_at' => $creditedAt,
            ]);
        }

        if (Carbon::parse($contract->end_date)->isPast()) {
            $contract->update(['status' => 'completed']);
        }
    }

    public function accrueInvestments(User $user): void
    {
        $subs = InvestmentSubscription::where('user_id', $user->id)->where('status', 'active')->with('product.asset', 'rewards')->get();

        foreach ($subs as $sub) {
            $this->accrueSubscription($sub);
        }
    }

    protected function accrueSubscription(InvestmentSubscription $sub): void
    {
        $product = $sub->product;
        $asset = $product->asset;
        $lastCredited = $sub->rewards()->max('credited_at');
        $since = $lastCredited ? Carbon::parse($lastCredited) : Carbon::parse($sub->start_date);
        $end = $sub->unlock_date ? Carbon::parse($sub->unlock_date)->min(now()) : now();

        $days = (int) $since->diffInDays($end);
        if ($days < 1) {
            return;
        }

        $wallet = WalletAccount::firstOrCreate(['user_id' => $sub->user_id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $house = House::wallet(WalletAccount::TYPE_INVESTMENT);
        $dailyRate = ((float) $product->apy_pct / 100) / 365;
        $intervalDays = $product->payout_frequency === 'weekly' ? 7 : 1;
        $periods = intdiv($days, $intervalDays);

        for ($i = 1; $i <= $periods; $i++) {
            $creditedAt = $since->copy()->addDays($i * $intervalDays);
            $amount = round((float) $sub->amount * $dailyRate * $intervalDays, 8);
            if ($amount <= 0) {
                continue;
            }

            $this->ledger->post(
                entries: [
                    ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
                ],
                referenceType: 'investment_reward',
                referenceId: $sub->id,
                description: "Investment reward for subscription #{$sub->id}",
                idempotencyKey: "investment-reward-{$sub->id}-".$creditedAt->format('Y-m-d'),
            );

            InvestmentReward::create([
                'investment_subscription_id' => $sub->id,
                'amount' => $amount,
                'credited_at' => $creditedAt,
            ]);
        }

        if ($sub->unlock_date && Carbon::parse($sub->unlock_date)->isPast()) {
            $sub->update(['status' => 'matured']);
        }
    }
}
