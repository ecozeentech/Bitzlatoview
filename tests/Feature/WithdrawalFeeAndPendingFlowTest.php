<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WalletAccount;
use App\Models\Withdrawal;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalFeeAndPendingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function fund(User $user, string $type, Asset $asset, float $amount): void
    {
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $type]);
        $house = House::wallet($type);

        app(LedgerService::class)->post(
            entries: [
                ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
            ],
            referenceType: 'test_funding',
        );
    }

    public function test_withdrawal_request_locks_full_amount_and_records_fee_and_net_amount(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_percentage'], ['value' => '2', 'type' => 'number']);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $usdt, 1000);

        $this->actingAs($user)->post(route('app.funding.withdraw.store'), [
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'payment_method_type' => 'crypto',
            'address' => 'TXsomeaddress',
            'amount' => 500,
        ])->assertRedirect();

        $withdrawal = Withdrawal::firstOrFail();
        $this->assertEquals(500, (float) $withdrawal->amount);
        $this->assertEquals(10, (float) $withdrawal->fee); // 2% of 500
        $this->assertEquals(490, (float) $withdrawal->net_amount);
        $this->assertEquals('pending_review', $withdrawal->status);

        $wallet = WalletAccount::where('user_id', $user->id)->where('type', 'primary')->first();
        // Full amount (not amount+fee) is locked immediately — available drops by exactly 500.
        $this->assertEquals(500, (float) $wallet->balanceFor($usdt)->available);
        $this->assertEquals(500, (float) $wallet->balanceFor($usdt)->locked);
    }

    public function test_completing_a_withdrawal_splits_fee_to_house_primary_and_rest_to_house_source_wallet(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_percentage'], ['value' => '2', 'type' => 'number']);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->fund($user, WalletAccount::TYPE_TRADING, $usdt, 1000);

        $this->actingAs($user)->post(route('app.funding.withdraw.store'), [
            'wallet_type' => 'trading',
            'asset_id' => $usdt->id,
            'payment_method_type' => 'crypto',
            'address' => 'TXsomeaddress',
            'amount' => 500,
        ])->assertRedirect();

        $withdrawal = Withdrawal::firstOrFail();

        $houseTradingBefore = (float) House::wallet(WalletAccount::TYPE_TRADING)->balanceFor($usdt)->available;
        $housePrimaryBefore = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($usdt)->available;

        $this->actingAs($admin)->post(route('admin.withdrawals.complete', $withdrawal))->assertRedirect();

        $withdrawal->refresh();
        $this->assertEquals('completed', $withdrawal->status);

        $wallet = WalletAccount::where('user_id', $user->id)->where('type', 'trading')->first();
        $this->assertEquals(0, (float) $wallet->balanceFor($usdt)->locked);
        // Funded with 1000, withdrew 500 — 500 remains available.
        $this->assertEquals(500, (float) $wallet->balanceFor($usdt)->available);

        $houseTradingAfter = (float) House::wallet(WalletAccount::TYPE_TRADING)->balanceFor($usdt)->fresh()->available;
        $housePrimaryAfter = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($usdt)->fresh()->available;

        // Net (490) goes to House's Trading wallet (mirroring the source), fee (10) always to House Primary.
        $this->assertEquals(490, $houseTradingAfter - $houseTradingBefore);
        $this->assertEquals(10, $housePrimaryAfter - $housePrimaryBefore);
    }

    public function test_rejecting_a_withdrawal_unlocks_the_full_amount_with_no_fee_taken(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_percentage'], ['value' => '2', 'type' => 'number']);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $usdt, 1000);

        $this->actingAs($user)->post(route('app.funding.withdraw.store'), [
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'payment_method_type' => 'crypto',
            'address' => 'TXsomeaddress',
            'amount' => 500,
        ])->assertRedirect();

        $withdrawal = Withdrawal::firstOrFail();

        $this->actingAs($admin)->post(route('admin.withdrawals.reject', $withdrawal), [
            'rejection_reason' => 'Could not verify identity',
        ])->assertRedirect();

        $wallet = WalletAccount::where('user_id', $user->id)->where('type', 'primary')->first();
        $this->assertEquals(1000, (float) $wallet->balanceFor($usdt)->available);
        $this->assertEquals(0, (float) $wallet->balanceFor($usdt)->locked);
    }

    public function test_withdrawal_fee_is_clamped_by_admin_configured_min_and_max(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_percentage'], ['value' => '1', 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_min'], ['value' => '5', 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_max'], ['value' => '8', 'type' => 'number']);

        // 1% of 100 = 1, below the 5 minimum -> fee clamped up to 5.
        $this->assertEquals(5.0, Withdrawal::calculateFee(100));
        // 1% of 2000 = 20, above the 8 maximum -> fee clamped down to 8.
        $this->assertEquals(8.0, Withdrawal::calculateFee(2000));
        // 1% of 600 = 6, within [5, 8] -> unchanged.
        $this->assertEquals(6.0, Withdrawal::calculateFee(600));
    }

    public function test_admin_can_disable_the_withdrawal_fee_entirely(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_enabled'], ['value' => '0', 'type' => 'boolean']);
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_percentage'], ['value' => '5', 'type' => 'number']);

        $this->assertEquals(0.0, Withdrawal::calculateFee(1000));
    }

    public function test_admin_can_update_withdrawal_fee_settings_via_the_settings_page(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.settings.withdrawal-fee.update'), [
            'enabled' => '1',
            'percentage' => '3.5',
            'min_fee' => '1',
            'max_fee' => '50',
        ])->assertRedirect();

        $this->assertEquals(3.5, (float) SystemSetting::getValue('withdrawal_fee_percentage'));
        $this->assertTrue((bool) SystemSetting::getValue('withdrawal_fee_enabled'));
        $this->assertEquals(1.0, (float) SystemSetting::getValue('withdrawal_fee_min'));
        $this->assertEquals(50.0, (float) SystemSetting::getValue('withdrawal_fee_max'));
    }
}
