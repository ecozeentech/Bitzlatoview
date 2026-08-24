<?php

namespace Tests\Feature;

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

    protected function fundPrimaryWallet(User $user, float $amount): void
    {
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);

        app(LedgerService::class)->post(
            entries: [
                ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $amount],
                ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $amount],
            ],
            referenceType: 'test_funding',
        );
    }

    public function test_allocation_is_rejected_when_amount_is_below_the_traders_minimum_copy_amount(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundPrimaryWallet($user, 1000);
        $trader = TraderProfile::first();
        $trader->update(['min_copy_amount' => 150]);

        $this->actingAs($user)->post(route('app.copy-trading.allocate', $trader), [
            'amount' => 100,
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('copy_allocations', 0);
    }

    public function test_valid_allocation_uses_primary_wallet_and_stores_the_traders_minimum(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundPrimaryWallet($user, 1000);
        $trader = TraderProfile::first();
        $trader->update(['min_copy_amount' => 150]);

        $this->actingAs($user)->post(route('app.copy-trading.allocate', $trader), [
            'amount' => 300,
        ])->assertRedirect();

        $allocation = CopyAllocation::firstOrFail();
        $this->assertEquals(300, (float) $allocation->amount);
        $this->assertEquals(150, (float) $allocation->minimum_amount);

        $wallet = WalletAccount::where('user_id', $user->id)->where('type', WalletAccount::TYPE_PRIMARY)->first();
        $usdt = Asset::where('symbol', 'USDT')->first();
        $this->assertEquals(300, (float) $wallet->balanceFor($usdt)->locked);
    }

    public function test_cannot_allocate_to_a_sold_out_trader(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fundPrimaryWallet($user, 1000);
        $trader = TraderProfile::first();
        $trader->update(['status' => 'sold_out']);

        $this->actingAs($user)->post(route('app.copy-trading.allocate', $trader), [
            'amount' => 200,
        ])->assertStatus(422);

        $this->assertDatabaseCount('copy_allocations', 0);
    }

    public function test_admin_can_toggle_trader_availability(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $trader = TraderProfile::first();
        $this->assertEquals('active', $trader->status);

        $this->actingAs($admin)->post(route('admin.copy-trading.toggle-availability', $trader))->assertRedirect();
        $this->assertEquals('sold_out', $trader->fresh()->status);

        $this->actingAs($admin)->post(route('admin.copy-trading.toggle-availability', $trader))->assertRedirect();
        $this->assertEquals('active', $trader->fresh()->status);
    }

    public function test_admin_can_edit_full_trader_profile_including_followers_count(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $trader = TraderProfile::first();

        $this->actingAs($admin)->patch(route('admin.copy-trading.update', $trader), [
            'display_name' => $trader->display_name,
            'category' => $trader->category,
            'risk_score' => 42,
            'return_30d_pct' => 12.5,
            'return_90d_pct' => 30,
            'max_drawdown_pct' => 5,
            'win_rate_pct' => 60,
            'followers_count' => 9999,
            'min_copy_amount' => 75,
            'lock_days' => 45,
            'status' => 'active',
        ])->assertRedirect();

        $trader->refresh();
        $this->assertEquals(9999, $trader->followers_count);
        $this->assertEquals(75, (float) $trader->min_copy_amount);
        $this->assertEquals(42, $trader->risk_score);
    }
}
