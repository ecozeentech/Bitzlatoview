<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\PaymentMethod;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Platform-wide deposit/withdrawal amount floors and ceilings, admin-configurable on top of
 * (for deposits) each payment method's own min/max — see
 * Admin\SettingsController::depositWithdrawalLimits() and FundingController.
 */
class DepositWithdrawalLimitsTest extends TestCase
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

    public function test_admin_can_view_the_deposit_and_withdrawal_limits_page(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.settings.deposit-withdrawal-limits'))->assertOk();
    }

    public function test_admin_can_update_global_deposit_and_withdrawal_limits(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.settings.deposit-withdrawal-limits.update'), [
            'deposit_min' => 20,
            'deposit_max' => 5000,
            'withdrawal_min' => 10,
            'withdrawal_max' => 2000,
        ])->assertRedirect();

        $this->assertEquals(20, SystemSetting::getValue('deposit_min_amount'));
        $this->assertEquals(5000, SystemSetting::getValue('deposit_max_amount'));
        $this->assertEquals(10, SystemSetting::getValue('withdrawal_min_amount'));
        $this->assertEquals(2000, SystemSetting::getValue('withdrawal_max_amount'));
    }

    public function test_regular_users_cannot_update_deposit_and_withdrawal_limits(): void
    {
        $this->seed();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post(route('admin.settings.deposit-withdrawal-limits.update'), [
            'deposit_min' => 20,
        ])->assertForbidden();
    }

    public function test_deposit_below_the_global_minimum_is_rejected(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'deposit_min_amount'], ['value' => '50', 'type' => 'number']);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $method = PaymentMethod::create([
            'name' => 'USDT Wallet', 'type' => 'crypto', 'currency' => 'USDT',
            'instructions' => 'Send USDT', 'min_amount' => 1, 'is_active' => true,
        ]);
        $user = User::factory()->create(['kyc_status' => 'approved']);

        $response = $this->actingAs($user)->post(route('app.funding.deposit.store'), [
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'payment_method_id' => $method->id,
            'amount' => 10, // below the payment method's own min (1) but below the global min (50)
            'proof_file' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('deposits', 0);
    }

    public function test_deposit_above_the_global_maximum_is_rejected(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'deposit_max_amount'], ['value' => '1000', 'type' => 'number']);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $method = PaymentMethod::create([
            'name' => 'USDT Wallet', 'type' => 'crypto', 'currency' => 'USDT',
            'instructions' => 'Send USDT', 'min_amount' => 1, 'is_active' => true,
        ]);
        $user = User::factory()->create(['kyc_status' => 'approved']);

        $response = $this->actingAs($user)->post(route('app.funding.deposit.store'), [
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'payment_method_id' => $method->id,
            'amount' => 5000,
            'proof_file' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('deposits', 0);
    }

    public function test_deposit_within_global_limits_succeeds(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'deposit_min_amount'], ['value' => '50', 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'deposit_max_amount'], ['value' => '1000', 'type' => 'number']);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $method = PaymentMethod::create([
            'name' => 'USDT Wallet', 'type' => 'crypto', 'currency' => 'USDT',
            'instructions' => 'Send USDT', 'min_amount' => 1, 'is_active' => true,
        ]);
        $user = User::factory()->create(['kyc_status' => 'approved']);

        $this->actingAs($user)->post(route('app.funding.deposit.store'), [
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'payment_method_id' => $method->id,
            'amount' => 500,
            'proof_file' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertRedirect();

        $this->assertDatabaseCount('deposits', 1);
    }

    public function test_withdrawal_below_the_global_minimum_is_rejected(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'withdrawal_min_amount'], ['value' => '25', 'type' => 'number']);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $usdt, 1000);

        $response = $this->actingAs($user)->post(route('app.funding.withdraw.store'), [
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'payment_method_type' => 'crypto',
            'address' => 'TXsomeaddress',
            'amount' => 5,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('withdrawals', 0);

        // Nothing should have been locked since the request was rejected before touching the ledger.
        $wallet = WalletAccount::where('user_id', $user->id)->where('type', 'primary')->first();
        $this->assertEquals(1000, (float) $wallet->balanceFor($usdt)->available);
    }

    public function test_withdrawal_above_the_global_maximum_is_rejected(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'withdrawal_max_amount'], ['value' => '100', 'type' => 'number']);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $usdt, 1000);

        $response = $this->actingAs($user)->post(route('app.funding.withdraw.store'), [
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'payment_method_type' => 'crypto',
            'address' => 'TXsomeaddress',
            'amount' => 500,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('withdrawals', 0);
    }

    public function test_withdrawal_within_global_limits_succeeds(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'withdrawal_min_amount'], ['value' => '25', 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'withdrawal_max_amount'], ['value' => '1000', 'type' => 'number']);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $usdt, 2000);

        $this->actingAs($user)->post(route('app.funding.withdraw.store'), [
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'payment_method_type' => 'crypto',
            'address' => 'TXsomeaddress',
            'amount' => 500,
        ])->assertRedirect();

        $this->assertDatabaseCount('withdrawals', 1);
    }

    public function test_deposit_and_withdraw_pages_render_with_limits_set(): void
    {
        $this->seed();
        SystemSetting::updateOrCreate(['key' => 'deposit_min_amount'], ['value' => '20', 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'deposit_max_amount'], ['value' => '5000', 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'withdrawal_min_amount'], ['value' => '10', 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'withdrawal_max_amount'], ['value' => '2000', 'type' => 'number']);

        $user = User::factory()->create(['kyc_status' => 'approved']);

        $this->actingAs($user)->get(route('app.funding.deposit'))->assertOk()->assertSee('Platform limit');
        $this->actingAs($user)->get(route('app.funding.withdraw'))->assertOk()->assertSee('Platform limit');
    }

    public function test_no_global_limits_set_means_any_positive_amount_is_accepted(): void
    {
        $this->seed();

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $usdt, 1000000);

        $this->actingAs($user)->post(route('app.funding.withdraw.store'), [
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'payment_method_type' => 'crypto',
            'address' => 'TXsomeaddress',
            'amount' => 999999,
        ])->assertRedirect();

        $this->assertDatabaseCount('withdrawals', 1);
    }
}
