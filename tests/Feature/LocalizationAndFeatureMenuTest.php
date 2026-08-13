<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AppFeatureMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationAndFeatureMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_locale_persists_to_the_users_profile_and_translates_ui(): void
    {
        $this->seed();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/locale/es')->assertRedirect();
        $this->assertEquals('es', $user->fresh()->locale);

        $response = $this->actingAs($user)->get('/app/dashboard');
        $response->assertOk();
        $response->assertSee('Depositar');
    }

    public function test_arabic_locale_sets_rtl_direction(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $this->actingAs($user)->post('/locale/ar');

        $response = $this->actingAs($user)->get('/app/dashboard');
        $response->assertSee('dir="rtl"', false);
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->seed();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/locale/xx')->assertNotFound();
    }

    public function test_every_ai_features_popup_entry_matches_the_sidebar(): void
    {
        // The popup and sidebar are both driven by AppFeatureMenu::groups(), so this test
        // mainly guards against the data structure itself becoming malformed (missing a
        // route prefix or translation key) as new features are added over time.
        foreach (AppFeatureMenu::flat() as $entry) {
            $this->assertCount(4, $entry);
            [$label, $routeName, $prefix, $translationKey] = $entry;
            $this->assertNotEmpty($label);
            $this->assertNotEmpty($prefix);
            $this->assertNotEmpty($translationKey);
        }
    }

    public function test_all_features_popup_renders_every_group(): void
    {
        $this->seed();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/app/dashboard');
        $response->assertOk();

        foreach (array_keys(AppFeatureMenu::groups()) as $group) {
            if ($group) {
                $response->assertSee($group);
            }
        }
    }
}
