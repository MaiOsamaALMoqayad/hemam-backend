<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Cross-site login: trading an academy token for a site admin token.
 */
class AcademySsoTest extends TestCase
{
    private const ACADEMY = 'https://academy-api.test';

    protected function setUp(): void
    {
        parent::setUp();

        // Only these tables: an older migration in this repo (dropping
        // activities.is_active) doesn't run on SQLite.
        $this->artisan('migrate', [
            '--path' => [
                'database/migrations/0001_01_01_000000_create_users_table.php',
                'database/migrations/2025_11_29_090905_create_personal_access_tokens_table.php',
                'database/migrations/2026_11_16_000001_add_academy_user_id_to_users_table.php',
            ],
        ]);

        config(['services.academy_sso.api_url' => self::ACADEMY]);
    }

    private function fakeAcademy(?array $me, int $status = 200): void
    {
        Http::fake([
            self::ACADEMY.'/api/v1/auth/me' => Http::response(
                $me === null ? ['message' => 'Unauthenticated.'] : ['success' => true, 'data' => $me],
                $status,
            ),
        ]);
    }

    private function linkedAdmin(int $academyUserId = 42): User
    {
        $user = User::factory()->create();
        $user->forceFill(['academy_user_id' => $academyUserId])->save();

        return $user;
    }

    public function test_linked_academy_user_gets_a_site_token(): void
    {
        $admin = $this->linkedAdmin();
        $this->fakeAcademy(['id' => 42, 'is_active' => true, 'site_user_id' => $admin->id]);

        $response = $this->postJson('/api/admin/sso/exchange', ['academy_token' => '9|academy']);

        $response->assertOk()->assertJsonPath('user.id', $admin->id);
        $token = $response->json('token');
        $this->assertNotEmpty($token);
        $this->assertNotNull($admin->tokens()->where('name', 'sso')->first()->expires_at);
        Http::assertSent(fn ($req) => $req->hasHeader('Authorization', 'Bearer 9|academy'));

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/user')
            ->assertOk()
            ->assertJsonPath('user.id', $admin->id)
            ->assertJsonPath('user.academy_user_id', 42);
    }

    public function test_link_must_exist_on_both_sides(): void
    {
        // The academy says "site user X", but X points at another academy user.
        $victim = $this->linkedAdmin(99);
        $this->fakeAcademy(['id' => 42, 'is_active' => true, 'site_user_id' => $victim->id]);
        $this->postJson('/api/admin/sso/exchange', ['academy_token' => '9|academy'])->assertForbidden();

        // This side is linked, the academy side isn't.
        $admin = $this->linkedAdmin(43);
        $this->fakeAcademy(['id' => 43, 'is_active' => true, 'site_user_id' => null]);
        $this->postJson('/api/admin/sso/exchange', ['academy_token' => '9|academy'])->assertForbidden();

        $this->assertSame(0, $victim->tokens()->count() + $admin->tokens()->count());
    }

    public function test_invalid_or_inactive_academy_account_is_rejected(): void
    {
        $admin = $this->linkedAdmin();

        $this->fakeAcademy(null, 401);
        $this->postJson('/api/admin/sso/exchange', ['academy_token' => 'bogus'])->assertForbidden();

        $this->fakeAcademy(['id' => 42, 'is_active' => false, 'site_user_id' => $admin->id]);
        $this->postJson('/api/admin/sso/exchange', ['academy_token' => '9|academy'])->assertForbidden();

        $this->assertSame(0, $admin->tokens()->count());
    }

    public function test_academy_token_is_required(): void
    {
        Http::fake();

        $this->postJson('/api/admin/sso/exchange', [])->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_link_command_sets_and_clears_the_link(): void
    {
        $admin = User::factory()->create(['email' => 'admin@rowwad.org']);
        $admin->createToken('sso');
        $admin->createToken('admin-token');

        $this->artisan('sso:link', ['email' => 'admin@rowwad.org', 'academy_user_id' => '5'])->assertSuccessful();
        $this->assertSame(5, $admin->fresh()->academy_user_id);

        $other = User::factory()->create(['email' => 'other@rowwad.org']);
        $this->artisan('sso:link', ['email' => 'other@rowwad.org', 'academy_user_id' => '5'])->assertFailed();
        $this->assertNull($other->fresh()->academy_user_id);

        $this->artisan('sso:link', ['email' => 'admin@rowwad.org', '--unlink' => true])->assertSuccessful();
        $this->assertNull($admin->fresh()->academy_user_id);
        $this->assertSame(['admin-token'], $admin->tokens()->pluck('name')->all());
    }
}
