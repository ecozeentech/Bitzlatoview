<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\MiningPackage;
use App\Models\TaxReport;
use App\Models\User;
use App\Models\WalletAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_tax_rate_and_estimated_amount_on_a_report(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $report = TaxReport::create([
            'user_id' => $user->id, 'year' => 2026, 'cost_basis_method' => 'fifo',
            'income_total' => 100, 'fees_paid' => 5, 'generated_at' => now(),
        ]);

        $this->actingAs($admin)->patch(route('admin.tax.update', $report), [
            'tax_rate_pct' => 20,
            'estimated_tax_owed' => 19,
        ])->assertRedirect();

        $report->refresh();
        $this->assertEquals(20, (float) $report->tax_rate_pct);
        $this->assertEquals(19, (float) $report->estimated_tax_owed);
    }

    public function test_mining_package_marked_sold_out_cannot_be_purchased(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $package = MiningPackage::first();

        $this->actingAs($admin)->post(route('admin.mining.toggle-availability', $package))->assertRedirect();
        $this->assertTrue($package->fresh()->is_sold_out);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_INVESTMENT]);
        $usdt = Asset::where('symbol', 'USDT')->first();
        $wallet->balanceFor($usdt)->update(['available' => 10000]);

        $this->actingAs($user)->post(route('app.mining.purchase', $package), [
            'reward_destination' => 'investment',
        ])->assertStatus(422);
    }

    public function test_admin_can_add_and_remove_funds_from_a_users_wallet_via_profile_page(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        // Balance adjustments now apply immediately — no second-admin approval step.
        $this->actingAs($admin)->post(route('admin.adjustments.store'), [
            'user_id' => $user->id,
            'wallet_type' => 'primary',
            'asset_id' => Asset::where('symbol', 'USDT')->first()->id,
            'direction' => 'credit',
            'amount' => 250,
            'reason' => 'Manual credit test',
        ])->assertRedirect();

        $adjustment = \App\Models\BalanceAdjustment::latest()->first();
        $this->assertEquals('applied', $adjustment->status);
        $this->assertEquals($admin->id, $adjustment->approved_by);

        $wallet = WalletAccount::where('user_id', $user->id)->where('type', 'primary')->first();
        $usdt = Asset::where('symbol', 'USDT')->first();
        $this->assertEquals(250, (float) $wallet->balanceFor($usdt)->available);

        // Debits reduce the same wallet immediately too.
        $this->actingAs($admin)->post(route('admin.adjustments.store'), [
            'user_id' => $user->id,
            'wallet_type' => 'primary',
            'asset_id' => $usdt->id,
            'direction' => 'debit',
            'amount' => 100,
            'reason' => 'Manual debit test',
        ])->assertRedirect();

        $this->assertEquals(150, (float) $wallet->balanceFor($usdt)->fresh()->available);
    }

    public function test_live_chat_widget_only_renders_when_fully_configured_and_enabled(): void
    {
        $this->seed();

        $this->get('/')->assertDontSee('embed.tawk.to');

        \App\Models\LiveChatSetting::current()->update([
            'property_id' => 'prop123',
            'widget_id' => 'default',
            'is_enabled' => true,
        ]);

        $this->get('/')->assertSee('embed.tawk.to/prop123/default', false);
    }
}
