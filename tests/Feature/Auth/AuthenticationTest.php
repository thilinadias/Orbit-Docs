<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use App\Providers\RouteServiceProvider;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    protected function createTestUser($overrides = [])
    {
        $user = User::factory()->create(array_merge([
            'password' => bcrypt('password'),
        ], $overrides));

        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
        ]);

        $role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $user->organizations()->attach($org->id, [
            'role_id' => $role->id,
            'is_primary' => true,
        ]);

        return $user;
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $user = $this->createTestUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $user = $this->createTestUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
