<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Balance;
use App\Models\User;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\TotpService;
use App\Support\House;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LockedBalanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function lockSomeFunds(User $user, Asset $asset, float $amount): WalletAccount
    {
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);
        $ledger = app(LedgerService::class);

        $ledger->post(
            entries: [
                ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
            ],
            referenceType: 'test_funding',
        );
        $ledger->lockFunds($wallet, $asset, (string) $amount);

        return $wallet;
    }

    public function test_admin_can_view_locked_balances_across_all_users(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $this->lockSomeFunds($user, $usdt, 300);

        $this->actingAs($admin)->get(route('admin.wallets.locked-balances.index'))
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee('300');
    }

    public function test_admin_can_unlock_a_stuck_locked_balance_back_to_available(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $wallet = $this->lockSomeFunds($user, $usdt, 300);
        $balance = Balance::where('wallet_account_id', $wallet->id)->where('asset_id', $usdt->id)->first();

        $this->actingAs($admin)->post(route('admin.wallets.locked-balances.unlock', $balance), [
            'reason' => 'Stuck due to a cancelled order bug',
        ])->assertRedirect();

        $balance->refresh();
        $this->assertEquals(0, (float) $balance->locked);
        $this->assertEquals(300, (float) $balance->available);
        $this->assertTrue(AuditLog::where('action', 'locked_balance.unlocked')->exists());
    }

    public function test_admin_can_directly_edit_a_locked_balance_amount(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $wallet = $this->lockSomeFunds($user, $usdt, 300);
        $balance = Balance::where('wallet_account_id', $wallet->id)->where('asset_id', $usdt->id)->first();

        $this->actingAs($admin)->post(route('admin.wallets.locked-balances.update', $balance), [
            'new_amount' => 50,
            'reason' => 'Correcting a duplicate lock from a matching engine bug',
        ])->assertRedirect();

        $balance->refresh();
        $this->assertEquals(50, (float) $balance->locked);
        // Available is untouched by a locked-balance correction — it's a standalone override.
        $this->assertEquals(0, (float) $balance->available);

        $log = AuditLog::where('action', 'locked_balance.edited')->firstOrFail();
        $this->assertEquals(300.0, (float) $log->before['locked']);
        $this->assertEquals(50.0, (float) $log->after['locked']);
    }

    public function test_reason_is_required_to_unlock_or_edit_a_locked_balance(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $wallet = $this->lockSomeFunds($user, $usdt, 300);
        $balance = Balance::where('wallet_account_id', $wallet->id)->where('asset_id', $usdt->id)->first();

        $this->actingAs($admin)->post(route('admin.wallets.locked-balances.unlock', $balance), [])
            ->assertSessionHasErrors('reason');

        $this->actingAs($admin)->post(route('admin.wallets.locked-balances.update', $balance), ['new_amount' => 10])
            ->assertSessionHasErrors('reason');
    }

    public function test_2fa_enabled_admin_must_provide_a_valid_totp_code_to_unlock_a_balance(): void
    {
        $this->seed();
        $totp = app(TotpService::class);
        $secret = $totp->generateSecret();
        $admin = User::factory()->create([
            'role' => 'admin',
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);
        $user = User::factory()->create();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $wallet = $this->lockSomeFunds($user, $usdt, 300);
        $balance = Balance::where('wallet_account_id', $wallet->id)->where('asset_id', $usdt->id)->first();

        // No code at all -> rejected, balance untouched.
        $this->actingAs($admin)->post(route('admin.wallets.locked-balances.unlock', $balance), [
            'reason' => 'test',
        ])->assertSessionHas('error');
        $this->assertEquals(300, (float) $balance->fresh()->locked);

        // Wrong code -> rejected.
        $this->actingAs($admin)->post(route('admin.wallets.locked-balances.unlock', $balance), [
            'reason' => 'test', 'totp_code' => '000000',
        ])->assertSessionHas('error');
        $this->assertEquals(300, (float) $balance->fresh()->locked);

        // Correct current code -> succeeds.
        $code = $this->currentTotpCode($totp, $secret);
        $this->actingAs($admin)->post(route('admin.wallets.locked-balances.unlock', $balance), [
            'reason' => 'test', 'totp_code' => $code,
        ])->assertSessionHas('success');
        $this->assertEquals(0, (float) $balance->fresh()->locked);
    }

    protected function currentTotpCode(TotpService $totp, string $secret): string
    {
        // TotpService's code generator is protected — reach it via reflection rather than
        // brute-forcing all 1M possible codes just to get a valid one for "now".
        $method = new \ReflectionMethod($totp, 'code');
        $method->setAccessible(true);
        $timeSlice = (int) floor(time() / 30);

        return $method->invoke($totp, $secret, $timeSlice);
    }
}
