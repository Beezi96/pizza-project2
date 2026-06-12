<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class CartFactoryTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_auth_user_can_view_cart(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/cart');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'product_id',
                    'quantity',
                ]
            ]
        ]);

        $response->assertJsonCount(1, 'data');

    }

    public function test_guest_cannot_view_cart(): void
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    }

    public function test_auth_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $token = JWTAuth::fromUser($user);

        $payload = [
            'product_id' => $product->id,
            'quantity' => 2,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cart/items', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => 2,
            ],
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_auth_user_cannot_add_more_than_10_pizzas(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->pizza()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 9,
        ]);

        $token = JWTAuth::fromUser($user);

        $payload = [
            'product_id' => $product->id,
            'quantity' => 2,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cart/items', $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Pizza limit 10.',
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 9,
        ]);
    }

    public function test_auth_user_can_update_cart_item_quantity(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->pizza()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $token = JWTAuth::fromUser($user);

        $payload = [
            'quantity' => 5,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/cart/items/' . $product->id, $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => 5,
            ],
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    public function test_auth_user_cannot_update_cart_item_above_limit(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->pizza()->create();
        $firstProduct = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        $secondProduct = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
            'quantity' => 4,
        ]);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $secondProduct->id,
            'quantity' => 5,
        ]);

        $token = JWTAuth::fromUser($user);
        $payload = [
            'quantity' => 6,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/cart/items/' . $firstProduct->id, $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Pizza limit 10.'
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
            'quantity' => 4,
        ]);
    }


    public function test_auth_user_can_delete_cart_item(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->pizza()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/cart/items/' . $product->id);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Deleted'
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_authenticated_user_gets_404_when_deleting_missing_cart_item(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->pizza()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/cart/items/' . $product->id);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Cart item not found.'
        ]);
    }

}


