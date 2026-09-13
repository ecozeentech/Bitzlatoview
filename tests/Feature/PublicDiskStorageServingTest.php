<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression test for the QR-code-and-every-other-public-image-not-showing bug: the
 * public/storage symlink `php artisan storage:link` creates is unreliable on a lot of shared
 * hosting (many control panels block symlinks outright, others silently drop them on redeploy),
 * so every image on the platform (QR codes, avatars, branding logos, NFT art) would 404 the
 * moment that symlink was missing. The permanent fix enables Laravel's built-in "serve"
 * mechanism on the `public` disk (see config/filesystems.php), which serves /storage/{path}
 * directly from storage/app/public without depending on the symlink existing at all.
 */
class PublicDiskStorageServingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_payment_method_qr_code_is_publicly_reachable_without_the_storage_symlink(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.payment-methods.store'), [
            'name' => 'BTC Wallet',
            'type' => 'crypto',
            'currency' => 'BTC',
            'network' => 'BTC',
            'address' => 'bc1qxyz1234567890',
            'min_amount' => 10,
            'instructions' => 'Send only BTC to this address.',
            'qr_code' => UploadedFile::fake()->image('qr.png'),
        ])->assertSessionHasNoErrors();

        $method = PaymentMethod::where('name', 'BTC Wallet')->firstOrFail();
        $this->assertNotNull($method->qr_code_path);

        // The public/storage symlink deliberately does not exist in this test environment,
        // proving the fix does not depend on it.
        $this->assertFileDoesNotExist(public_path('storage'));

        $response = $this->get('/storage/'.$method->qr_code_path);

        $response->assertOk();
        $this->assertStringStartsWith('image/', $response->headers->get('Content-Type'));
    }

    public function test_a_missing_public_disk_file_returns_404(): void
    {
        $this->get('/storage/payment-method-qr/does-not-exist.png')->assertNotFound();
    }

    public function test_private_local_disk_files_are_not_reachable_through_the_public_storage_route(): void
    {
        Storage::disk('local')->put('deposit-proofs/secret.txt', 'super secret proof data');

        // The `local` disk (KYC documents, deposit proofs) is deliberately not served — it is
        // only ever exposed through authenticated admin controller actions.
        $this->get('/storage/deposit-proofs/secret.txt')->assertNotFound();
    }
}
