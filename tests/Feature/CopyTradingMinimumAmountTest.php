<?php

namespace Tests\Feature;

use App\Http\Controllers\App\CopyTradingController;
use App\Models\Asset;
use App\Models\CopyAllocation;
use App\Models\TraderProfile;
use App\Models\User;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CopyTradingMinimumAmountTest extends TestCase
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

    public function test_allocation_is_rejected_when_minimum_amount_is_below_the_admin_configured_global_floor(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundInvestmentWallet($user, 1000);
        $trader = TraderProfile::first();

        $this->actingAs($user)->post(route('app.copy-trading.allocate', $trader), [
            'amount' => 200,
            'minimum_amount' => 50, // below the $100 default global floor
        ])->assertSessionHasErrors('minimum_amount');

        $this->assertDatabaseCount('copy_allocations', 0);
    }

    public function test_allocation_is_rejected_when_amount_is_below_the_users_own_chosen_minimum(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundInvestmentWallet($user, 1000);
        $trader = TraderProfile::first();

        $this->actingAs($user)->post(route('app.copy-trading.allocate', $trader), [
            'amount' => 100,
            'minimum_amount' => 150,
        ])->assertRedirect();

        $this->assertDatabaseCount('copy_allocations', 0);
    }

    public function test_valid_allocation_stores_both_amount_and_minimum_amount(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundInvestmentWallet($user, 1000);
        $trader = TraderProfile::first();

        $this->actingAs($user)->post(route('app.copy-trading.allocate', $trader), [
            'amount' => 300,
            'minimum_amount' => 150,
        ])->assertRedirect();

        $allocation = CopyAllocation::firstOrFail();
        $this->assertEquals(300, (float) $allocation->amount);
        $this->assertEquals(150, (float) $allocation->minimum_amount);
    }

    public function test_admin_can_change_the_global_minimum_and_it_is_enforced_immediately(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.settings.copy-trading-min-amount'), [
            'copy_trading_min_amount' => 500,
        ])->assertRedirect();

        $this->assertEquals(500.0, CopyTradingController::globalMinimumAmount());

        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundInvestmentWallet($user, 1000);
        $trader = TraderProfile::first();

        $this->actingAs($user)->post(route('app.copy-trading.allocate', $trader), [
            'amount' => 600,
            'minimum_amount' => 499, // below the new $500 floor
        ])->assertSessionHasErrors('minimum_amount');
    }
}
