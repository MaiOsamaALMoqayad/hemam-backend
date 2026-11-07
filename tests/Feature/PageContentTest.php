<?php

namespace Tests\Feature;

use App\Models\PageContent;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PageContentTest extends TestCase
{
    /**
     * Only the tables this feature needs: an older migration in this repo
     * (dropping activities.is_active) doesn't run on SQLite, so the full
     * RefreshDatabase migrate can't be used for in-memory tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => [
                'database/migrations/0001_01_01_000000_create_users_table.php',
                'database/migrations/2025_11_29_090905_create_personal_access_tokens_table.php',
                'database/migrations/2026_11_07_000001_create_page_contents_table.php',
            ],
        ]);
        PageContent::clearCache();
    }

    public function test_public_endpoint_returns_saved_values_as_an_object(): void
    {
        $this->getJson('/api/v1/content')
            ->assertOk()
            ->assertExactJson(['success' => true, 'data' => []]);

        PageContent::create(['key' => 'home.hero.title', 'value' => 'عنوان']);
        PageContent::clearCache();

        $this->getJson('/api/v1/content')
            ->assertOk()
            ->assertJsonPath('data', ['home.hero.title' => 'عنوان']);
    }

    public function test_guest_cannot_save(): void
    {
        $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'home.hero.title', 'value' => 'x']],
        ])->assertUnauthorized();
    }

    public function test_admin_saves_strips_html_and_empty_value_restores_default(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/admin/content', [
            'contents' => [
                ['key' => 'home.hero.title', 'value' => "<b>سطر أول</b>\r\n<script>x</script>سطر ثانٍ  "],
                ['key' => 'home.hero.subtitle', 'value' => 'وصف'],
            ],
        ])->assertOk()
            ->assertJsonPath('data', [
                'home.hero.subtitle' => 'وصف',
                'home.hero.title'    => "سطر أول\nxسطر ثانٍ",
            ]);

        $this->assertDatabaseHas('page_contents', ['key' => 'home.hero.title', 'updated_by' => $user->id]);

        $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'home.hero.subtitle', 'value' => '']],
        ])->assertOk();

        $this->assertDatabaseMissing('page_contents', ['key' => 'home.hero.subtitle']);
        $this->assertSame(
            ['home.hero.title'],
            array_keys($this->getJson('/api/v1/content')->json('data'))
        );
    }

    public function test_unknown_key_and_overlong_text_are_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'home.evil', 'value' => 'x']],
        ])->assertStatus(422)->assertJsonValidationErrors('contents.0.key');

        $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'home.hero.title', 'value' => str_repeat('ا', 151)]],
        ])->assertStatus(422)->assertJsonValidationErrors('contents.0.value');

        $this->assertDatabaseCount('page_contents', 0);
    }
}
