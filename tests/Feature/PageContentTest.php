<?php

namespace Tests\Feature;

use App\Models\PageContent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_links_are_validated_and_returned_as_objects(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/admin/content', [
            'contents' => [
                ['key' => 'home.hero.cta_primary', 'value' => ['label' => '<i>برامجنا</i>', 'url' => '/programs']],
                ['key' => 'footer.social.facebook', 'value' => ['label' => 'ignored', 'url' => 'https://facebook.com/x']],
            ],
        ])->assertOk()
            ->assertJsonPath('data', [
                'footer.social.facebook' => ['label' => '', 'url' => 'https://facebook.com/x'],
                'home.hero.cta_primary'  => ['label' => 'برامجنا', 'url' => '/programs'],
            ]);

        foreach (['javascript:alert(1)', '//evil.com', 'evil.com', 'https://a b'] as $url) {
            $this->putJson('/api/admin/content', [
                'contents' => [['key' => 'home.hero.cta_primary', 'value' => ['label' => 'x', 'url' => $url]]],
            ])->assertStatus(422)->assertJsonValidationErrors('contents.0.value');
        }

        // A labelled link needs its label; an empty link restores the default.
        $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'home.hero.cta_primary', 'value' => ['label' => '', 'url' => '/x']]],
        ])->assertStatus(422);

        $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'home.hero.cta_primary', 'value' => ['label' => '', 'url' => '']]],
        ])->assertOk();
        $this->assertDatabaseMissing('page_contents', ['key' => 'home.hero.cta_primary']);

        // Text keys don't take objects, image keys don't take values here.
        $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'home.hero.title', 'value' => ['label' => 'x']]],
        ])->assertStatus(422);
        $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'site.logo', 'value' => 'page-content/x.png']],
        ])->assertStatus(422);
    }

    public function test_image_upload_replaces_and_restores(): void
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            $this->markTestSkipped('Needs gd or imagick for Intervention Image.');
        }

        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $first = $this->post('/api/admin/content/image', [
            'key'   => 'site.logo',
            'image' => UploadedFile::fake()->image('logo.png', 800, 400),
        ], ['Accept' => 'application/json'])->assertOk();

        $firstPath = PageContent::where('key', 'site.logo')->value('value');
        $this->assertStringEndsWith('.png', $firstPath);
        Storage::disk('public')->assertExists($firstPath);
        [$width] = getimagesizefromstring(Storage::disk('public')->get($firstPath));
        $this->assertSame(400, $width); // scaled down to the key's width
        $this->assertStringEndsWith($firstPath, $first->json('data.url'));

        $this->post('/api/admin/content/image', [
            'key'   => 'site.logo',
            'image' => UploadedFile::fake()->image('logo.jpg', 200, 100),
        ], ['Accept' => 'application/json'])->assertOk();

        $secondPath = PageContent::where('key', 'site.logo')->value('value');
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'site.logo', 'value' => '']],
        ])->assertOk();
        Storage::disk('public')->assertMissing($secondPath);
        $this->assertDatabaseCount('page_contents', 0);
    }

    public function test_image_upload_rejects_non_image_keys_and_guests(): void
    {
        $this->post('/api/admin/content/image', [], ['Accept' => 'application/json'])
            ->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());

        $this->post('/api/admin/content/image', [
            'key'   => 'home.hero.title',
            'image' => UploadedFile::fake()->create('x.svg', 10, 'image/svg+xml'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['key', 'image']);
    }
}
