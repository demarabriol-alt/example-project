<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_users()
    {
        User::factory()->count(3)->create();
        
        $response = $this->getJson('/api/users');
        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_store_creates_user()
    {
        $response = $this->postJson('/api/users', [
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

        $response = $this->getJson("/api/users/{$user->id}");
        $response->assertStatus(200)
                 ->assertJsonPath('id', $user->id);
    }

    public function test_update_modifies_user()
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('name', 'Updated Name');
    }

    public function test_destroy_deletes_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
