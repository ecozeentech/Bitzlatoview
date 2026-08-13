<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\SignalPackage;
use App\Models\SignalSubscription;
use App\Models\User;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalsTest extends TestCase
{
    use RefreshDatabase;

    protected function fundInvestmentWallet(User $user, float $amount): void
    {
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_INVESTMENT);

        app(LedgerService::class)->post(
            entries: [
                ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $amount],
                ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $amount],
            ],
            referenceType: 'test_funding',
        );
    }

    public function test_user_can_subscribe_to_a_signal_package_and_funds_are_locked(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundInvestmentWallet($user, 1000);
        $package = SignalPackage::first();

        $this->actingAs($user)->post(route('app.signals.subscribe', $package), [
            'amount' => 200,
        ])->assertRedirect();

        $subscription = SignalSubscription::firstOrFail();
        $this->assertEquals(200, (float) $subscription->amount);
        $this->assertNotNull($subscription->entry_price);
        $this->assertEquals('active', $subscription->status);
    }

    public function test_subscription_cannot_be_stopped_before_the_lock_period_ends(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundInvestmentWallet($user, 1000);
        $package = SignalPackage::first();

        $this->actingAs($user)->post(route('app.signals.subscribe', $package), ['amount' => 200]);
        $subscription = SignalSubscription::firstOrFail();

        $this->actingAs($user)->post(route('app.signals.stop', $subscription))->assertRedirect();
        $this->assertEquals('active', $subscription->fresh()->status);
    }

    public function test_stopping_an_unlocked_subscription_settles_pnl_and_releases_funds(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundInvestmentWallet($user, 1000);
        $package = SignalPackage::first();

        $this->actingAs($user)->post(route('app.signals.subscribe', $package), ['amount' => 200]);
        $subscription = SignalSubscription::firstOrFail();
        $subscription->update(['unlocks_at' => now()->subDay()]);

        $this->actingAs($user)->post(route('app.signals.stop', $subscription))->assertRedirect();

        $subscription->refresh();
        $this->assertEquals('stopped', $subscription->status);
        $this->assertNotNull($subscription->exit_price);

        $wallet = WalletAccount::where('user_id', $user->id)->where('type', WalletAccount::TYPE_INVESTMENT)->first();
        $usdt = Asset::where('symbol', 'USDT')->first();
        // Principal + settled P&L should be back in available balance (started with 1000, locked 200).
        $this->assertEquals(1000 + $subscription->pnl, (float) $wallet->balanceFor($usdt)->available);
    }

    public function test_admin_can_create_and_manage_signal_packages(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.signals.store'), [
            'name' => 'Admin Test Signal',
            'tracked_asset_symbol' => 'ETH',
            'status' => 'active',
            'risk_level' => 'moderate',
            'expected_return_pct' => 8,
            'duration_days' => 14,
            'min_investment' => 25,
            'fee_pct' => 1,
        ])->assertRedirect();

        $package = SignalPackage::where('name', 'Admin Test Signal')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.signals.toggle', $package))->assertRedirect();
        $this->assertEquals('paused', $package->fresh()->status);
    }

    public function test_admin_pnl_adjustment_posts_a_ledger_correction_and_audit_log(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundInvestmentWallet($user, 1000);
        $package = SignalPackage::first();

        $this->actingAs($user)->post(route('app.signals.subscribe', $package), ['amount' => 200]);
        $subscription = SignalSubscription::firstOrFail();
        $subscription->update(['unlocks_at' => now()->subDay()]);
        $this->actingAs($user)->post(route('app.signals.stop', $subscription));
        $subscription->refresh();

        $wallet = WalletAccount::where('user_id', $user->id)->where('type', WalletAccount::TYPE_INVESTMENT)->first();
        $usdt = Asset::where('symbol', 'USDT')->first();
        $balanceBefore = (float) $wallet->balanceFor($usdt)->available;

        $this->actingAs($admin)->post(route('admin.signals.subscriptions.adjust', $subscription), [
            'new_pnl' => (float) $subscription->pnl + 10,
            'reason' => 'Test correction',
        ])->assertRedirect();

        $this->assertEquals((float) $subscription->pnl + 10, (float) $subscription->fresh()->pnl);
        $this->assertEquals($balanceBefore + 10, (float) $wallet->balanceFor($usdt)->fresh()->available);
        $this->assertTrue(\App\Models\AuditLog::where('action', 'signal.pnl_adjusted')->exists());
    }
}
