<?php

namespace Tests\Feature;

use App\Models\PageContent;
use App\Models\Partner;
use App\Models\Statistics;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * In-place card editing on the public site: the homepage goal cards (a
 * page_contents list), statistics, partners and trainers.
 */
class CardEditingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Before migrating: the partner import migration writes to this disk.
        Storage::fake('public');

        // Only these tables: an older migration in this repo (dropping
        // activities.is_active) doesn't run on SQLite.
        $this->artisan('migrate', [
            '--path' => [
                'database/migrations/0001_01_01_000000_create_users_table.php',
                'database/migrations/2025_11_29_090905_create_personal_access_tokens_table.php',
                'database/migrations/2025_11_29_120749_create_trainers_table.php',
                'database/migrations/2025_11_29_120815_create_statistics_table.php',
                'database/migrations/2026_05_17_203815_create_partners_table.php',
                'database/migrations/2026_05_20_152749_create_goals_table.php',
                'database/migrations/2026_11_07_000001_create_page_contents_table.php',
                'database/migrations/2026_11_08_000001_add_order_index_to_statistics_table.php',
                'database/migrations/2026_11_08_000002_import_builtin_partner_logos.php',
            ],
        ]);
        PageContent::clearCache();
    }

    private function actingAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    private function saveGoalCards(mixed $value)
    {
        return $this->putJson('/api/admin/content', [
            'contents' => [['key' => 'home.goals.cards', 'value' => $value]],
        ]);
    }

    public function test_goal_cards_are_saved_cleaned_and_kept_when_empty(): void
    {
        $this->actingAsAdmin();

        $this->saveGoalCards([
            ['id' => 'a1', 'icon' => 'Target', 'title' => '<b>بناء</b> القدرات', 'description' => 'وصف'],
            ['id' => 'bad id!', 'icon' => '', 'title' => 'ثانٍ', 'description' => null],
        ])->assertOk();

        $cards = $this->getJson('/api/v1/content')->json('data')['home.goals.cards'];
        $this->assertSame(['id' => 'a1', 'icon' => 'Target', 'title' => 'بناء القدرات', 'description' => 'وصف'], $cards[0]);
        $this->assertSame('ثانٍ', $cards[1]['title']);
        $this->assertSame('', $cards[1]['description']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{12}$/', $cards[1]['id']); // invalid id replaced

        // Deleting every card keeps an empty list (the section hides)...
        $this->saveGoalCards([])->assertOk();
        $this->assertSame([], $this->getJson('/api/v1/content')->json('data')['home.goals.cards']);

        // ...while "" brings the built-in cards back.
        $this->saveGoalCards('')->assertOk();
        $this->assertDatabaseMissing('page_contents', ['key' => 'home.goals.cards']);
    }

    public function test_goal_cards_are_validated(): void
    {
        $this->actingAsAdmin();

        $this->saveGoalCards([['icon' => 'NotAnIcon', 'title' => 'x']])->assertStatus(422);
        $this->saveGoalCards([['icon' => 'Target', 'title' => '']])->assertStatus(422);
        $this->saveGoalCards([['title' => str_repeat('ا', 81)]])->assertStatus(422);
        $this->saveGoalCards(array_fill(0, 13, ['title' => 'x']))->assertStatus(422);
        $this->saveGoalCards(['a' => ['title' => 'x']])->assertStatus(422); // not a list
        $this->assertDatabaseCount('page_contents', 0);
    }

    public function test_statistics_are_ordered_and_reordered(): void
    {
        $this->actingAsAdmin();

        foreach (['أ', 'ب', 'ج'] as $title) {
            $this->postJson('/api/admin/statistics', ['title' => $title, 'count' => '10', 'icon_name' => 'Users'])
                ->assertCreated();
        }
        $ids = Statistics::orderBy('id')->pluck('id')->all();
        $this->assertSame([1, 2, 3], Statistics::orderBy('id')->pluck('order_index')->all());

        $this->postJson('/api/admin/reorder/statistics', ['ids' => [$ids[2], $ids[0], $ids[1]]])->assertOk();
        $this->assertSame(['ج', 'أ', 'ب'], array_column($this->getJson('/api/v1/statistics')->json('data'), 'title'));

        // Must list every id exactly once.
        $this->postJson('/api/admin/reorder/statistics', ['ids' => [$ids[0], $ids[1]]])->assertStatus(422);
        $this->postJson('/api/admin/reorder/unknown', ['ids' => [1]])->assertNotFound();
    }

    public function test_builtin_partner_logos_are_imported_in_order(): void
    {
        $partners = Partner::orderBy('order_index')->get();

        $this->assertCount(9, $partners);
        $this->assertSame('partners/builtin-p2.jpg', $partners->first()->image);
        $this->assertSame('partners/builtin-p10.jpg', $partners->last()->image);
        foreach ($partners as $partner) {
            Storage::disk('public')->assertExists($partner->image);
        }
    }

    public function test_partners_can_be_renamed_and_reordered(): void
    {
        $this->actingAsAdmin();
        $ids = Partner::orderBy('order_index')->pluck('id')->all();

        $this->post("/api/admin/partners/{$ids[0]}", ['name' => 'شريك أ'], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.name', 'شريك أ');

        $reversed = array_reverse($ids);
        $this->postJson('/api/admin/reorder/partners', ['ids' => $reversed])->assertOk();
        $this->assertSame($reversed, array_column($this->getJson('/api/v1/partners')->json('data'), 'id'));

        $this->post('/api/admin/partners/9999', ['name' => 'x'], ['Accept' => 'application/json'])->assertNotFound();
    }

    public function test_partner_image_can_be_replaced(): void
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            $this->markTestSkipped('Needs gd or imagick for Intervention Image.');
        }
        $this->actingAsAdmin();
        $partner = Partner::orderBy('order_index')->first();
        $old = $partner->image;

        $this->post("/api/admin/partners/{$partner->id}", [
            'image' => UploadedFile::fake()->image('new.png', 800, 600),
        ], ['Accept' => 'application/json'])->assertOk();

        $partner->refresh();
        $this->assertNotSame($old, $partner->image);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($partner->image);
    }

    public function test_trainer_reorder_clears_the_public_cache(): void
    {
        $this->actingAsAdmin();
        $a = Trainer::create(['name' => ['ar' => 'أحمد'], 'bio' => ['ar' => ''], 'image' => 'trainers/a.jpg', 'order' => 1, 'is_active' => true]);
        $b = Trainer::create(['name' => ['ar' => 'بكر'], 'bio' => ['ar' => ''], 'image' => 'trainers/b.jpg', 'order' => 2, 'is_active' => true]);

        $this->assertSame(['أحمد', 'بكر'], array_column($this->getJson('/api/v1/trainers')->json('data'), 'name'));
        $this->assertTrue(Cache::has('trainers:all'));

        $this->postJson('/api/admin/reorder/trainers', ['ids' => [$b->id, $a->id]])->assertOk();
        $this->assertSame(['بكر', 'أحمد'], array_column($this->getJson('/api/v1/trainers')->json('data'), 'name'));
    }

    public function test_about_page_goals_are_reordered(): void
    {
        $this->actingAsAdmin();

        foreach (['أول', 'ثانٍ', 'ثالث'] as $text) {
            $this->postJson('/api/admin/goals', ['text' => $text])->assertCreated();
        }
        $ids = \App\Models\Goal::orderBy('order_index')->pluck('id')->all();

        $this->postJson('/api/admin/reorder/goals', ['ids' => [$ids[1], $ids[2], $ids[0]]])->assertOk();
        $this->assertSame(['ثانٍ', 'ثالث', 'أول'], array_column($this->getJson('/api/v1/goals')->json('data'), 'text'));
    }

    public function test_guests_cannot_reorder_or_update(): void
    {
        $this->postJson('/api/admin/reorder/statistics', ['ids' => [1]])->assertUnauthorized();
        $this->post('/api/admin/partners/1', ['name' => 'x'], ['Accept' => 'application/json'])->assertUnauthorized();
    }
}
