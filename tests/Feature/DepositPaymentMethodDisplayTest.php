<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * On the user-facing deposit page, the payment method <option> should show only the display
 * name (+ network, for crypto methods) — not the currency code appended after it.
 */
class DepositPaymentMethodDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_page_payment_method_options_show_only_name_and_network(): void
    {
        $this->seed();
        $user = User::factory()->create(['kyc_status' => 'approved']);

        $crypto = PaymentMethod::create([
            'name' => 'USDT Wallet', 'type' => 'crypto', 'currency' => 'USDT', 'network' => 'TRC20',
            'instructions' => 'Send USDT', 'min_amount' => 1, 'is_active' => true,
        ]);
        $bank = PaymentMethod::create([
            'name' => 'Chase Bank Wire', 'type' => 'bank_transfer', 'currency' => 'USD',
            'instructions' => 'Wire transfer', 'min_amount' => 50, 'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('app.funding.deposit'));

        $response->assertOk();
        $response->assertSee('<option value="'.$crypto->id.'">USDT Wallet (TRC20)</option>', false);
        $response->assertSee('<option value="'.$bank->id.'">Chase Bank Wire</option>', false);
        $response->assertDontSee('USDT Wallet (TRC20) (USDT)');
        $response->assertDontSee('Chase Bank Wire (USD)');
    }
}
