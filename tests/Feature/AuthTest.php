<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $this->postJson('/api/auth/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertCreated()
        ->assertJsonStructure(['user', 'token']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create();
        $user->assignRole('operator');

        $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])
        ->assertOk()
        ->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'wrongpassword',
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('operator');

        $this->getJson('/api/auth/me', [
            'Authorization' => 'Bearer ' . $user->api_token,
        ])
        ->assertOk()
        ->assertJsonPath('email', $user->email);
    }
}
