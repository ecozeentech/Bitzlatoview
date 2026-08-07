<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VirtualCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VirtualCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_request_starts_pending_and_activates_only_after_admin_approval(): void
    {
        $this->seed();

        $user = User::factory()->create(['kyc_status' => 'approved']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post('/app/virtual-cards', [
            'nickname' => 'Travel Card',
            'spending_limit' => 500,
            'currency' => 'USD',
        ])->assertRedirect();

        $card = VirtualCard::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('pending', $card->status);

        // A pending card cannot be revealed or funded yet.
        $this->actingAs($user)->post("/app/virtual-cards/{$card->id}/reveal")->assertStatus(422);

        $this->actingAs($admin)->post("/admin/virtual-cards/{$card->id}/approve")->assertRedirect();

        $card->refresh();
        $this->assertSame('active', $card->status);
        $this->assertNotNull($card->approved_at);
        $this->assertGreaterThan(0, $card->transactions()->count());

        $this->actingAs($user)->post("/app/virtual-cards/{$card->id}/reveal")->assertOk()->assertJsonStructure(['number', 'cvv']);
    }

    public function test_card_request_respects_admin_configured_max_limit(): void
    {
        $this->seed();

        $user = User::factory()->create(['kyc_status' => 'approved']);

        \App\Models\CardSetting::current()->update(['max_spending_limit' => 1000]);

        $this->actingAs($user)->post('/app/virtual-cards', [
            'nickname' => 'Over Limit',
            'spending_limit' => 5000,
            'currency' => 'USD',
        ])->assertSessionHasErrors('spending_limit');

        $this->assertDatabaseCount('virtual_cards', 0);
    }
}
