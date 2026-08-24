<?php

namespace Tests\Feature\Admin;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_payment_method_with_a_qr_code_upload(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.payment-methods.store'), [
            'name' => 'BTC Wallet',
            'type' => 'crypto',
            'currency' => 'BTC',
            'network' => 'BTC',
            'address' => 'bc1qxyz1234567890',
            'min_amount' => 10,
            'instructions' => 'Send only BTC to this address.',
            'qr_code' => UploadedFile::fake()->image('qr.png'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $method = PaymentMethod::where('name', 'BTC Wallet')->first();
        $this->assertNotNull($method);
        $this->assertNotNull($method->qr_code_path);
    }

    public function test_admin_can_update_a_payment_methods_qr_code(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $method = PaymentMethod::create([
            'name' => 'ETH Wallet', 'type' => 'crypto', 'currency' => 'ETH',
            'instructions' => 'Send ETH', 'min_amount' => 5, 'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.payment-methods.update', $method), [
            '_method' => 'PATCH',
            'name' => 'ETH Wallet',
            'type' => 'crypto',
            'currency' => 'ETH',
            'instructions' => 'Send ETH only.',
            'min_amount' => 5,
            'qr_code' => UploadedFile::fake()->image('qr2.png'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertNotNull($method->fresh()->qr_code_path);
    }
}
