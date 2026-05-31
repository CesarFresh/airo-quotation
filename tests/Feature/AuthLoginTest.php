<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_jwt(): void
    {
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@airo.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'demo@airo.com',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
        ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@airo.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'demo@airo.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()->assertJson(['message' => 'Invalid credentials']);
    }
}