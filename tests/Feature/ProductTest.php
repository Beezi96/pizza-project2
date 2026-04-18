<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_products_index_returns_200(): void
    {
        $this->seed();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function test_products_show_returns_404_product_not_found(): void
    {
        $this->seed();

        $response = $this->getJson('api/products/999');

        $response->assertStatus(404);
        $response->assertJson([
           'message' => 'Product not found',
        ]);
    }

    public function test_product_show_returns_200(): void
    {
        $this->seed();

        $product = Product::query()->first();

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
        ]);
    }

    public function test_admin_can_create_product(): void
    {
        $this->seed();

        $admin = User::query()->where('is_admin', true)->first();
        $category = Category::query()->where('code', 'pizza')->first();

        $token = JWTAuth::fromUser($admin);

        $payload = [
            'name' => 'test pizza',
            'price' => 777,
            'description' => 'test pizza',
            'img' => null,
            'category_id' => $category->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products' , $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => 'test pizza',
                'category_id' => $category->id,
            ],
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'test pizza',
            'category_id' => $category->id,
        ]);
    }

    public function test_regular_user_cannot_create_product(): void
    {
        $this->seed();

        $user= User::query()->where('is_admin', false)->first();
        $category = Category::query()->where('code', 'pizza')->first();

        $token = JWTAuth::fromUser($user);

        $payload = [
            'name' => 'test pizza',
            'price' => 777,
            'description' => 'test pizza',
            'img' => null,
            'category_id' => $category->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin only.',
        ]);

        $this->assertDatabaseMissing('products', [
            'name' => 'test pizza',
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $this->seed();

        $admin = User::query()->where('is_admin', true)->first();
        $product = Product::query()->first();
        $category = Category::query()->where('code', 'drink')->first();

        $token = JWTAuth::fromUser($admin);

        $payload = [
            'name' => 'test update pizza',
            'price' => 999,
            'description' => 'new test pizza',
            'img' => null,
            'category_id' => $category->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $token)
            ->putJson('/api/products/' . $product->id, $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => 'test update pizza',
                'category_id' => $category->id,
            ],
        ]);
        $this->assertDatabaseHas('products', [
           'id' => $product->id,
           'name' => 'test update pizza',
           'price' => 999,
           'description' => 'new test pizza',
           'img' => null,
           'category_id' => $category->id,
        ]);

    }

    public function test_regular_user_cannot_update_product(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();
        $product = Product::query()->first();
        $category = Category::query()->where('code', 'drink')->first();

        $token = JWTAuth::fromUser($user);

        $payload = [
            'name' => 'Not updated',
            'price' => 111,
            'description' => 'User not updated',
            'img' => null,
            'category_id' => $category->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '. $token)
            ->putJson('/api/products/' . $product->id, $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin only.',
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'name' => 'Not updated',
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $this->seed();

        $admin = User::query()->where('is_admin', true)->first();
        $product = Product::query()->first();

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer '. $token)
            ->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Product deleted successfully',
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_regular_user_cannot_delete_product(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();
        $product = Product::query()->first();

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer '. $token)
            ->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin only.',
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    }
}
