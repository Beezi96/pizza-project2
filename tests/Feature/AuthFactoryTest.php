<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthFactoryTest extends TestCase
{
    use refreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_register(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'register-test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'register-test@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $payload = [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);

    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        // 1. Подготовка данных
        $user = User::factory()->create([
            'email' => 'login-user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        // 2. Действие
        $response = $this->postJson('/api/auth/login', $payload);

        // 3. Проверка
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
        ]);
        $response->assertJson([
            'message' => 'Login successfully.',
            'token_type' => 'bearer',
        ]);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        // 1. Подготовка данных
        $user = User::factory()->create([
            'email' => 'wrong-pass@example.com',
            'password' => bcrypt('password123'),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => 'wrong-password',
        ];

        // 2. Действие
        $response = $this->postJson('/api/auth/login', $payload);

        // 3. Проверка
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid email or password',
        ]);
    }

    public function test_authenticated_user_can_get_profile_me(): void
    {
        // 1. Подготовка данных
        $user = User::factory()->create([
            'name' => 'Profile User',
            'email' => 'profile@example.com',
        ]);

        $token = JWTAuth::fromUser($user);

        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/me');

        // 3. Проверка
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'name' => 'Profile User',
            'email' => 'profile@example.com',
        ]);
    }

    public function test_guest_cannot_get_profile_me(): void
    {
        // 1. Подготовка данных
        // Токен не передаем.

        // 2. Действие
        $response = $this->postJson('/api/auth/me');

        // 3. Проверка
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        // 1. Подготовка данных
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        // 3. Проверка
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully logged out',
        ]);
    }

    public function test_guest_cannot_logout(): void
    {
        // 1. Подготовка данных
        // Без токена.

        // 2. Действие
        $response = $this->postJson('/api/auth/logout');

        // 3. Проверка
        $response->assertStatus(401);
    }
}
