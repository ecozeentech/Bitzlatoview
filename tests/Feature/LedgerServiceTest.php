<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_credits_and_debits_update_balances_through_ledger(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
            'kyc_status' => 'approved',
            'email_verified_at' => now(),
        ]);
        app(WalletProvisioningService::class)->provision($user);

        $asset = Asset::query()->create([
            'symbol' => 'USDT',
            'name' => 'Tether',
            'type' => 'crypto',
            'mock_price_usd' => 1,
            'is_active' => true,
        ]);

        $wallet = $user->walletAccount('PRIMARY');
        $ledger = app(LedgerService::class);

        $ledger->creditAvailable($wallet, $asset, '100', 'test', 'credit-1');
        $ledger->debitAvailable($wallet, $asset, '40', 'test', 'debit-1');

        $balance = $wallet->balances()->where('asset_id', $asset->id)->first();

        $this->assertSame('60.00000000', (string) $balance->available);
        $this->assertDatabaseCount('ledger_entries', 2);
    }
}
