<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every entry in partials.app-sidebar links to a bare prefix (e.g. '/app/p2p') via
     * url($prefix), not a named route — so it's easy to add a feature whose routes only
     * cover sub-paths (like /app/p2p/buy) and forget the index itself. When that happens
     * the main nav link 404s for every user. This guards against that regressing.
     */
    public function test_every_sidebar_link_resolves_without_a_404(): void
    {
        $this->seed();

        $user = User::factory()->create(['kyc_status' => 'approved']);

        $prefixes = [
            'app/dashboard', 'app/markets', 'app/spot', 'app/buy-sell', 'app/swap',
            'app/futures', 'app/stocks', 'app/forex', 'app/metatrader-5',
            'app/p2p', 'app/copy-trading', 'app/ai-bots', 'app/mining', 'app/investments', 'app/nft',
            'app/wallet/primary', 'app/wallet/trading', 'app/wallet/investment',
            'app/funding/deposit', 'app/funding/withdraw', 'app/funding/transactions',
            'app/virtual-cards', 'app/tax', 'app/analyst-packages', 'app/news', 'app/blog',
            'app/referrals', 'app/support', 'app/settings',
        ];

        foreach ($prefixes as $prefix) {
            $response = $this->actingAs($user)->get('/'.$prefix);
            $this->assertContains(
                $response->getStatusCode(),
                [200, 302],
                "Sidebar link '/{$prefix}' returned {$response->getStatusCode()} (expected 200 or a redirect, not a 404)."
            );
        }
    }

    public function test_p2p_sidebar_link_specifically_renders_the_buy_page(): void
    {
        $this->seed();

        $user = User::factory()->create(['kyc_status' => 'approved']);

        $this->actingAs($user)->get('/app/p2p')
            ->assertOk()
            ->assertSee('Buy Crypto')
            ->assertSee('Sell Crypto');
    }
}
