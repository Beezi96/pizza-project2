<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_auth_user_can_view_cart(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();

        // Используется в документации!!! В проекте использивать этот вариант
        // $token = auth()->login($user);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/cart');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data'
        ]);
    }

    public function test_guest_cannot_view_cart(): void
    {
        $this->seed();

        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    }

    public function test_auth_user_can_add_product_to_cart(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();
        $pizza = Product::query()
            ->whereHas('category', function ($query) {
              $query->where('code', 'pizza');
            })
            ->first();

        $token = JWTAuth::fromUser($user);

        $payload = [
            'product_id' => $pizza->id,
            'quantity' => 2,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cart/items', $payload);


        $response->assertStatus(201);

        $response->assertJson([
            'data' => [
                'user_id' => $user->id,
                'product_id' => $pizza->id,
                'quantity' => 2,
            ]
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $pizza->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_cannot_add_more_than_10_pizzas_to_cart(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();
        $pizza = Product::query()
            ->whereHas('category', function ($query) {
                $query->where('code', 'pizza');
            })
            ->first();

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $pizza->id,
            'quantity' => 9,
        ]);

        $token = JWTAuth::fromUser($user);

        $payload = [
            'product_id' => $pizza->id,
            'quantity' => 2,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cart/items', $payload);

        $response->assertStatus(422);

        $response->assertJson([
            'message' => 'Pizza limit 10.'
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $pizza->id,
            'quantity' => 9,
        ]);
    }

    public function test_auth_user_can_update_cart_item_quantity(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();
        $pizza = Product::query()
            ->whereHas('category', function ($query) {
                $query->where('code', 'pizza');
            })
            ->first();

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $pizza->id,
            'quantity' => 2,
        ]);

        $token = JWTAuth::fromUser($user);

        $payload = [
            'quantity' => 5,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/cart/items/' . $pizza->id, $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'user_id' => $user->id,
                'product_id' => $pizza->id,
                'quantity' => 5,
            ],
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $pizza->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_cannot_update_cart_item_quantity(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();
        $pizza = Product::query()
            ->whereHas('category', function ($query) {
                $query->where('code', 'pizza');
            })
            ->take(2)
            ->get();

        $firstPizza = $pizza[0];
        $secondPizza = $pizza[1];

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $firstPizza->id,
            'quantity' => 4,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $secondPizza->id,
            'quantity' => 5,
        ]);

        $token = JWTAuth::fromUser($user);

        $payload = [
            'quantity' => 6,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/cart/items/' . $firstPizza->id, $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Pizza limit 10.'
        ]);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $firstPizza->id,
            'quantity' => 4,
        ]);
    }

    public function test_user_auth_user_can_delete_item(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();
        $pizza = Product::query()
            ->whereHas('category', function ($query) {
                $query->where('code', 'pizza');
            })
            ->first();

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $pizza->id,
            'quantity' => 3,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/cart/items/' . $pizza->id);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Deleted'
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_id' => $pizza->id,
        ]);
    }

    public function test_auth_user_gets_404_when_deleting_missing_cart_item(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();
        $pizza = Product::query()
            ->whereHas('category', function ($query) {
                $query->where('code', 'pizza');
            })
            ->first();

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/cart/items/' . $pizza->id);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Cart item not found.',
        ]);
    }


}
