<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletSuspensionAndImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_a_wallet_and_it_blocks_transfers_out(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => 'investment']);
        $usdt = Asset::where('symbol', 'USDT')->first();
        $house = House::wallet('investment');
        app(LedgerService::class)->post(
            entries: [
                ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => 500],
                ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => 500],
            ],
            referenceType: 'test_funding',
        );

        $this->actingAs($admin)->post(route('admin.users.wallets.toggle-suspension', [$user, 'investment']), [
            'reason' => 'Under review',
        ])->assertRedirect();

        $this->assertTrue($wallet->fresh()->is_suspended);

        $this->actingAs($user)->post(route('app.wallet.transfer'), [
            'from_type' => 'investment',
            'to_type' => 'primary',
            'asset_id' => $usdt->id,
            'amount' => 100,
        ])->assertRedirect();

        $this->assertEquals(500, (float) $wallet->fresh()->balanceFor($usdt)->available);
    }

    public function test_admin_can_unsuspend_a_wallet_restoring_transfers(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => 'primary']);
        $usdt = Asset::where('symbol', 'USDT')->first();
        $house = House::wallet('primary');
        app(LedgerService::class)->post(
            entries: [
                ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => 200],
                ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => 200],
            ],
            referenceType: 'test_funding',
        );

        $this->actingAs($admin)->post(route('admin.users.wallets.toggle-suspension', [$user, 'primary']));
        $this->actingAs($admin)->post(route('admin.users.wallets.toggle-suspension', [$user, 'primary']));
        $this->assertFalse($wallet->fresh()->is_suspended);

        $this->actingAs($user)->post(route('app.wallet.transfer'), [
            'from_type' => 'primary',
            'to_type' => 'trading',
            'asset_id' => $usdt->id,
            'amount' => 50,
        ])->assertRedirect();

        $this->assertEquals(150, (float) $wallet->fresh()->balanceFor($usdt)->available);
    }

    public function test_admin_can_impersonate_a_user_and_exit_back_to_their_own_account(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.login-as', $user))->assertRedirect('/app/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertEquals($admin->id, session('impersonator_id'));

        $this->post(route('stop-impersonating'))->assertRedirect();
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('impersonator_id'));
    }

    public function test_admin_cannot_impersonate_another_admin(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.login-as', $otherAdmin))->assertForbidden();
    }

    public function test_admin_can_edit_and_delete_notes(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.notes.store', $user), ['note' => 'Original note'])->assertRedirect();
        $note = \App\Models\AdminNote::latest()->first();

        $this->actingAs($admin)->patch(route('admin.users.notes.update', $note), ['note' => 'Updated note'])->assertRedirect();
        $this->assertEquals('Updated note', $note->fresh()->note);

        $this->actingAs($admin)->delete(route('admin.users.notes.destroy', $note))->assertRedirect();
        $this->assertDatabaseMissing('admin_notes', ['id' => $note->id]);
    }
}
