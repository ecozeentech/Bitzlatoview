<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\KycReview;
use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KycManualApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manually_approve_kyc_for_a_user_with_no_submission_at_all(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['kyc_status' => 'not_started']);

        $this->assertDatabaseMissing('kyc_submissions', ['user_id' => $user->id]);

        $this->actingAs($admin)->post(route('admin.kyc.manual-approve', $user), [
            'reason' => 'Verified in person at our partner branch office.',
        ])->assertRedirect();

        $user->refresh();
        $this->assertEquals('approved', $user->kyc_status);

        $submission = KycSubmission::where('user_id', $user->id)->firstOrFail();
        $this->assertEquals('approved', $submission->status);
        $this->assertTrue($submission->manually_verified);
        $this->assertEquals('Verified in person at our partner branch office.', $submission->manual_approval_reason);
        $this->assertNull($submission->government_id_path);

        $this->assertTrue(KycReview::where('kyc_submission_id', $submission->id)->where('decision', 'approved')->exists());
        $this->assertTrue(AuditLog::where('action', 'kyc.manually_approved')->exists());
    }

    public function test_admin_can_manually_approve_an_existing_incomplete_submission(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['kyc_status' => 'submitted']);
        $submission = KycSubmission::create([
            'user_id' => $user->id,
            'legal_name' => 'Jane Doe',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.kyc.manual-approve', $user), [
            'reason' => 'Confirmed identity over a verified video call.',
        ])->assertRedirect();

        $submission->refresh();
        $this->assertEquals('approved', $submission->status);
        $this->assertTrue($submission->manually_verified);
        $this->assertEquals('approved', $user->fresh()->kyc_status);
    }

    public function test_reason_is_required_to_manually_approve_kyc(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.kyc.manual-approve', $user), [])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseMissing('users', ['id' => $user->id, 'kyc_status' => 'approved']);
    }

    public function test_regular_users_cannot_manually_approve_kyc(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->post(route('admin.kyc.manual-approve', $other), [
            'reason' => 'Trying to self-approve',
        ])->assertForbidden();
    }
}
