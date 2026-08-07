<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\P2PAd;
use App\Models\P2POrder;
use App\Models\User;
use App\Models\WalletAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class P2PTradingTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_buyer_can_complete_a_full_p2p_trade_lifecycle(): void
    {
        $this->seed();

        $buyer = User::factory()->create(['kyc_status' => 'approved']);
        $ad = P2PAd::where('side', 'sell')->firstOrFail();
        $seller = $ad->user;

        $this->actingAs($buyer)->post('/app/p2p/orders', [
            'ad_id' => $ad->id,
            'crypto_amount' => 25,
            'payment_method' => 'Bank Transfer',
        ])->assertRedirect();

        $order = P2POrder::latest()->firstOrFail();
        $this->assertSame($buyer->id, $order->buyer_id);
        $this->assertSame($seller->id, $order->seller_id);
        $this->assertSame('escrow_locked', $order->status);

        $this->actingAs($buyer)->post("/app/p2p/orders/{$order->id}/mark-paid")->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);

        $this->actingAs($seller)->post("/app/p2p/orders/{$order->id}/release")->assertRedirect();
        $order->refresh();

        $this->assertSame('completed', $order->status);

        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $buyerWallet = WalletAccount::where('user_id', $buyer->id)->where('type', WalletAccount::TYPE_PRIMARY)->firstOrFail();
        $this->assertEquals(25, (float) $buyerWallet->balanceFor($usdt)->available);
    }

    public function test_unverified_user_cannot_open_a_p2p_order(): void
    {
        $this->seed();

        $buyer = User::factory()->create(['kyc_status' => 'not_started']);
        $ad = P2PAd::where('side', 'sell')->firstOrFail();

        $this->actingAs($buyer)->post('/app/p2p/orders', [
            'ad_id' => $ad->id,
            'crypto_amount' => 25,
            'payment_method' => 'Bank Transfer',
        ])->assertRedirect('/app/settings/kyc');

        $this->assertDatabaseCount('p2p_orders', 0);
    }

    public function test_order_fails_gracefully_when_seller_lacks_backing_balance(): void
    {
        $this->seed();

        $buyer = User::factory()->create(['kyc_status' => 'approved']);
        $ad = P2PAd::where('side', 'sell')->firstOrFail();
        $ad->update(['available_amount' => 999999]);

        // Drain the seller's real balance without touching the ad's declared amount,
        // reproducing the scenario the ad still claims more than it can back.
        $seller = $ad->user;
        $wallet = WalletAccount::where('user_id', $seller->id)->where('type', WalletAccount::TYPE_PRIMARY)->first();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $wallet->balanceFor($usdt)->update(['available' => 0]);

        $this->actingAs($buyer)->post('/app/p2p/orders', [
            'ad_id' => $ad->id,
            'crypto_amount' => 25,
            'payment_method' => 'Bank Transfer',
        ])->assertRedirect();

        $this->assertDatabaseCount('p2p_orders', 0);
    }
}
