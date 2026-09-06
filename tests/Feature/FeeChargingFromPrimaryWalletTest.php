<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\CardSetting;
use App\Models\MarketPair;
use App\Models\MiningContract;
use App\Models\MiningPackage;
use App\Models\SignalPackage;
use App\Models\SignalSubscription;
use App\Models\User;
use App\Models\VirtualCard;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\PricingService;
use App\Services\RewardAccrualService;
use App\Support\House;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Platform policy: every service fee is charged from the user's Primary Wallet (never
 * Trading or Investment), with revenue always landing in the House's Primary Wallet.
 */
class FeeChargingFromPrimaryWalletTest extends TestCase
{
    use RefreshDatabase;

    protected function fund(User $user, string $type, Asset $asset, float $amount): void
    {
        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $type]);
        $house = House::wallet($type);

        app(LedgerService::class)->post(
            entries: [
                ['wallet_account_id' => $house->id, 'asset_id' => $asset->id, 'direction' => 'debit', 'amount' => $amount],
                ['wallet_account_id' => $wallet->id, 'asset_id' => $asset->id, 'direction' => 'credit', 'amount' => $amount],
            ],
            referenceType: 'test_funding',
        );
    }

    public function test_spot_trading_fee_is_charged_from_each_traders_primary_wallet_not_trading_wallet(): void
    {
        $this->seed();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $btc = Asset::where('symbol', 'BTC')->firstOrFail();
        $market = MarketPair::where('symbol', 'BTC-USDT')->firstOrFail();
        $market->update(['maker_fee_pct' => 0.1, 'taker_fee_pct' => 0.1]);

        $seller = User::factory()->create(['kyc_status' => 'approved']);
        $buyer = User::factory()->create(['kyc_status' => 'approved']);

        $this->fund($seller, WalletAccount::TYPE_TRADING, $btc, 1);
        $this->fund($seller, WalletAccount::TYPE_PRIMARY, $usdt, 1);
        $this->fund($buyer, WalletAccount::TYPE_TRADING, $usdt, 100);
        $this->fund($buyer, WalletAccount::TYPE_PRIMARY, $usdt, 1);

        $housePrimaryUsdtBefore = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($usdt)->available;
        $houseTradingUsdtBefore = (float) House::wallet(WalletAccount::TYPE_TRADING)->balanceFor($usdt)->available;

        // Seller rests a limit sell order for 1 BTC @ 100 USDT.
        $this->actingAs($seller)->post('/app/spot/BTC-USDT/orders', [
            'side' => 'sell', 'type' => 'limit', 'quantity' => 1, 'price' => 100,
        ])->assertRedirect();

        // Buyer takes it with a matching limit buy.
        $this->actingAs($buyer)->post('/app/spot/BTC-USDT/orders', [
            'side' => 'buy', 'type' => 'limit', 'quantity' => 1, 'price' => 100,
        ])->assertRedirect();

        $buyerTrading = WalletAccount::where('user_id', $buyer->id)->where('type', WalletAccount::TYPE_TRADING)->first();
        $buyerPrimary = WalletAccount::where('user_id', $buyer->id)->where('type', WalletAccount::TYPE_PRIMARY)->first();
        $sellerTrading = WalletAccount::where('user_id', $seller->id)->where('type', WalletAccount::TYPE_TRADING)->first();
        $sellerPrimary = WalletAccount::where('user_id', $seller->id)->where('type', WalletAccount::TYPE_PRIMARY)->first();

        // Trading wallets moved exactly the principal — no fee markup baked in.
        $this->assertEquals(0, (float) $buyerTrading->balanceFor($usdt)->available);
        $this->assertEquals(1, (float) $buyerTrading->balanceFor($btc)->available);
        $this->assertEquals(100, (float) $sellerTrading->balanceFor($usdt)->available);
        $this->assertEquals(0, (float) $sellerTrading->balanceFor($btc)->available);

        // Each side's fee (0.1% of 100 = 0.1) was charged separately from their Primary Wallet.
        $this->assertEquals(0.9, round((float) $buyerPrimary->balanceFor($usdt)->available, 8));
        $this->assertEquals(0.9, round((float) $sellerPrimary->balanceFor($usdt)->available, 8));

        $housePrimaryUsdtAfter = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($usdt)->fresh()->available;
        $houseTradingUsdtAfter = (float) House::wallet(WalletAccount::TYPE_TRADING)->balanceFor($usdt)->fresh()->available;

        $this->assertEqualsWithDelta(0.2, $housePrimaryUsdtAfter - $housePrimaryUsdtBefore, 0.00000001);
        $this->assertEqualsWithDelta(0, $houseTradingUsdtAfter - $houseTradingUsdtBefore, 0.00000001);
    }

    public function test_spot_trade_still_settles_even_if_a_traders_primary_wallet_cannot_cover_the_fee(): void
    {
        $this->seed();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $btc = Asset::where('symbol', 'BTC')->firstOrFail();

        $seller = User::factory()->create(['kyc_status' => 'approved']);
        $buyer = User::factory()->create(['kyc_status' => 'approved']);

        $this->fund($seller, WalletAccount::TYPE_TRADING, $btc, 1);
        // Deliberately do NOT fund either Primary wallet — the fee should be waived, not block the trade.
        $this->fund($buyer, WalletAccount::TYPE_TRADING, $usdt, 100);

        $this->actingAs($seller)->post('/app/spot/BTC-USDT/orders', [
            'side' => 'sell', 'type' => 'limit', 'quantity' => 1, 'price' => 100,
        ])->assertRedirect();

        $this->actingAs($buyer)->post('/app/spot/BTC-USDT/orders', [
            'side' => 'buy', 'type' => 'limit', 'quantity' => 1, 'price' => 100,
        ])->assertRedirect();

        $buyerTrading = WalletAccount::where('user_id', $buyer->id)->where('type', WalletAccount::TYPE_TRADING)->first();
        $this->assertEquals(1, (float) $buyerTrading->balanceFor($btc)->available);
        $this->assertTrue(AuditLog::where('action', 'fee.waived_insufficient_primary_balance')->exists());
    }

    public function test_mining_maintenance_fee_is_charged_from_primary_wallet_and_full_reward_is_credited(): void
    {
        $this->seed();
        $btc = Asset::where('symbol', 'BTC')->firstOrFail();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();

        $user = User::factory()->create(['kyc_status' => 'approved']);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $btc, 10);

        $package = MiningPackage::firstOrFail();
        $package->update(['asset_id' => $btc->id, 'estimated_daily_reward_pct' => 1, 'maintenance_fee_pct' => 10]);

        $contract = MiningContract::create([
            'user_id' => $user->id,
            'mining_package_id' => $package->id,
            'amount_invested' => 1000,
            'reward_destination' => 'investment',
            'status' => 'active',
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(30),
        ]);

        $housePrimaryBtcBefore = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($btc)->available;

        app(RewardAccrualService::class)->accrueMining($user);

        $investmentWallet = WalletAccount::where('user_id', $user->id)->where('type', WalletAccount::TYPE_INVESTMENT)->first();
        $primaryWallet = WalletAccount::where('user_id', $user->id)->where('type', WalletAccount::TYPE_PRIMARY)->first();

        $creditedDays = $contract->rewards()->count();
        $this->assertGreaterThan(0, $creditedDays);

        $dailyReward = 1000 * 0.01; // 1000 invested * 1% daily reward
        $dailyFee = $dailyReward * 0.10; // 10% maintenance fee

        // Full gross reward credited to Investment (no fee skimmed off it anymore).
        $this->assertEqualsWithDelta($dailyReward * $creditedDays, (float) $investmentWallet->balanceFor($btc)->available, 0.00000001);
        // Maintenance fee charged separately from Primary Wallet (started with 10 BTC).
        $this->assertEqualsWithDelta(10 - ($dailyFee * $creditedDays), (float) $primaryWallet->balanceFor($btc)->available, 0.00000001);

        $housePrimaryBtcAfter = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($btc)->fresh()->available;
        $this->assertEqualsWithDelta($dailyFee * $creditedDays, $housePrimaryBtcAfter - $housePrimaryBtcBefore, 0.00000001);
    }

    public function test_signal_performance_fee_is_charged_from_primary_wallet_not_deducted_from_pnl(): void
    {
        $this->seed();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);

        $this->fund($user, WalletAccount::TYPE_INVESTMENT, $usdt, 1000);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $usdt, 50);

        $package = SignalPackage::first();
        $package->update(['fee_pct' => 5]);

        $housePrimaryBefore = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($usdt)->available;

        $this->actingAs($user)->post(route('app.signals.subscribe', $package), ['amount' => 200])->assertRedirect();
        $subscription = SignalSubscription::firstOrFail();
        $subscription->update(['unlocks_at' => now()->subDay()]);

        $this->actingAs($user)->post(route('app.signals.stop', $subscription))->assertRedirect();

        $primaryWallet = WalletAccount::where('user_id', $user->id)->where('type', WalletAccount::TYPE_PRIMARY)->first();
        // Fee = 5% of 200 = 10 USDT, charged from Primary (started with 50).
        $this->assertEquals(40, round((float) $primaryWallet->balanceFor($usdt)->available, 8));

        $housePrimaryAfter = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($usdt)->fresh()->available;
        $this->assertEquals(10, round($housePrimaryAfter - $housePrimaryBefore, 8));
    }

    public function test_virtual_card_issuance_and_funding_fees_are_charged_from_primary_wallet(): void
    {
        $this->seed();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);

        CardSetting::current()->update(['issuance_fee' => 5, 'funding_fee_pct' => 2]);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $usdt, 1000);
        $housePrimaryBefore = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($usdt)->available;

        $this->actingAs($user)->post('/app/virtual-cards', [
            'nickname' => 'Fee Test', 'spending_limit' => 500, 'currency' => 'USD',
        ])->assertRedirect();

        $card = VirtualCard::where('user_id', $user->id)->firstOrFail();
        $primaryWallet = WalletAccount::where('user_id', $user->id)->where('type', WalletAccount::TYPE_PRIMARY)->first();

        // Issuance fee of 5 USDT charged immediately.
        $this->assertEquals(995, round((float) $primaryWallet->balanceFor($usdt)->fresh()->available, 8));

        $card->update(['status' => 'active']);
        $this->actingAs($user)->post("/app/virtual-cards/{$card->id}/fund", ['amount' => 100])->assertRedirect();

        // Funding of 100 (from Primary, same as before) + 2% funding fee (2 USDT) also from Primary.
        $this->assertEquals(995 - 100 - 2, round((float) $primaryWallet->balanceFor($usdt)->fresh()->available, 8));

        // House Primary received the issuance fee (5) + funding fee (2) + the funded amount
        // itself (100), since card funding always transfers from the user's Primary Wallet.
        $housePrimaryAfter = (float) House::wallet(WalletAccount::TYPE_PRIMARY)->balanceFor($usdt)->fresh()->available;
        $this->assertEquals(5 + 2 + 100, round($housePrimaryAfter - $housePrimaryBefore, 8));
    }

    public function test_swap_fee_is_charged_from_primary_wallet_and_full_converted_amount_is_received(): void
    {
        $this->seed();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $btc = Asset::where('symbol', 'BTC')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);

        $btcPrice = app(PricingService::class)->usdPrice($btc);

        $this->fund($user, WalletAccount::TYPE_TRADING, $usdt, 1000);
        $tradingWallet = WalletAccount::where('user_id', $user->id)->where('type', WalletAccount::TYPE_TRADING)->first();
        $gross = 500 / $btcPrice;
        $fee = round($gross * (0.25 / 100), 18);
        $this->fund($user, WalletAccount::TYPE_PRIMARY, $btc, $fee + 0.0001);

        $this->actingAs($user)->post('/app/swap', [
            'wallet_type' => 'trading',
            'from_asset_id' => $usdt->id,
            'to_asset_id' => $btc->id,
            'amount' => 500,
        ])->assertRedirect();

        $primaryWallet = WalletAccount::where('user_id', $user->id)->where('type', WalletAccount::TYPE_PRIMARY)->first();
        // The swap itself credited the full converted amount (no markdown) to the Trading wallet.
        $this->assertEqualsWithDelta($gross, (float) $tradingWallet->balanceFor($btc)->fresh()->available, 0.00000001);
        // The fee was charged separately, from Primary, in the destination asset (BTC).
        $this->assertEqualsWithDelta(0.0001, (float) $primaryWallet->balanceFor($btc)->fresh()->available, 0.00000001);
    }

    public function test_swap_is_blocked_if_primary_wallet_cannot_cover_the_fee(): void
    {
        $this->seed();
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $btc = Asset::where('symbol', 'BTC')->firstOrFail();
        $user = User::factory()->create(['kyc_status' => 'approved']);

        $this->fund($user, WalletAccount::TYPE_TRADING, $usdt, 1000);
        // No BTC funded into Primary at all — fee cannot be covered.

        $this->actingAs($user)->post('/app/swap', [
            'wallet_type' => 'trading',
            'from_asset_id' => $usdt->id,
            'to_asset_id' => $btc->id,
            'amount' => 500,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('swap_transactions', 0);
    }
}
