<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function authHeaderForPermission(string $permission): array
    {
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        $token = $user->createToken('test-token')->plainTextToken;

        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_index_returns_all_users()
    {
        User::factory()->count(2)->create();

        $response = $this->withHeaders(
            $this->authHeaderForPermission('view users')
        )->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_store_creates_user()
    {
        $response = $this->withHeaders(
            $this->authHeaderForPermission('create users')
        )->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('name', 'Test User')
                 ->assertJsonPath('email', 'test@example.com');

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_show_returns_user()
    {
        $user = User::factory()->create();

        $response = $this->withHeaders(
            $this->authHeaderForPermission('view users')
        )->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('id', $user->id);
    }

    public function test_update_modifies_user()
    {
        $user = User::factory()->create();

        $response = $this->withHeaders(
            $this->authHeaderForPermission('update users')
        )->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('name', 'Updated Name');
    }

    public function test_destroy_deletes_user()
    {
        $user = User::factory()->create();

        $response = $this->withHeaders(
            $this->authHeaderForPermission('delete users')
        )->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_forbidden_without_required_permission()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/users');

        $response->assertForbidden();
    }
}
