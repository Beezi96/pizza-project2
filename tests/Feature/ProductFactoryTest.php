<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class ProductFactoryTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_created_products(): void
    {
        // 1. Подготовка данных
        $pizzaCategory = Category::factory()->pizza()->create();
        Product::factory()->count(3)->create([
            'category_id' => $pizzaCategory->id,
        ]);
        // 2. Действие
        $response = $this->getJson('/api/products');

        // 3.Проверка
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'price',
                    'description',
                    'img',
                    'category_id',
                ],
            ],
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_show_existing_product(): void
    {
        // 1. Подготовка

        $pizzaCategory = Category::factory()->pizza()->create();

        $product = Product::factory()->create([
            'name' => 'Margarita test',
            'price' => 450,
            'description' => 'description pizza test',
            'img' => null,
            'category_id' => $pizzaCategory->id,
        ]);

        // 2. Действие

        $response = $this->getJson('/api/products/' . $product->id);

        // 3. Проверка
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
              'id' => $product->id,
              'name' => 'Margarita test',
              'price' => 450,
              'description' => 'description pizza test',
              'img' => null,
              'category_id' => $pizzaCategory->id,
            ],
        ]);
    }

    public function test_show_404_product(): void
    {
        // 1. Подготовка данных
        // Ничего специально создовать не нужно

        // 2. Действие
        $response = $this->getJson('/api/products/99');

        // 3. Проверка
        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Product not found',
        ]);

    }

    public function test_admin_can_create_product(): void
    {
        // 1. Подготовка данных
        $pizzaCategory = Category::factory()->pizza()->create();
        $admin = User::factory()->admin()->create();
        $token = JWTAuth::fromUser($admin);

        $payload = [
          'name' => 'Pizza factory test',
          'price' => 650,
          'description' => 'Product created admin',
            'img' => null,
            'category_id' => $pizzaCategory->id,
        ];

        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', $payload);

        // 3. Проверка
        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => 'Pizza factory test',
                'price' => 650,
                'description' => 'Product created admin',
                'img' => null,
                'category_id' => $pizzaCategory->id,
            ],
        ]);

        $this->assertDatabaseHas('products', $payload);
    }

    public function test_regular_user_cannot_create_product(): void
    {
        // 1. Подготовка данных
        $pizzaCategory = Category::factory()->pizza()->create();
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $payload = [
            'name' => 'Regular factory test',
            'price' => 500,
            'description' => 'The user must not create a product',
            'img' => null,
            'category_id' => $pizzaCategory->id,
        ];

        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', $payload);

        // 3. Проверка
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin only.'
        ]);

        $this->assertDatabaseMissing('products', $payload);
    }

    public function test_admin_can_update_product(): void
    {
        // 1. Подготовка данных
        $pizzaCategory = Category::factory()->pizza()->create();
        $drinkCategory = Category::factory()->drink()->create();

        $admin = User::factory()->admin()->create();
        $token = JWTAuth::fromUser($admin);

        $product = Product::factory()->create([
            'name' => 'Old product',
            'price' => 400,
            'description' => 'Old description',
            'img' => null,
            'category_id' => $pizzaCategory->id,
        ]);

        $payload = [
            'name' => 'Updated product',
            'price' => 550,
            'description' => 'Updated description',
            'img' => null,
            'category_id' => $drinkCategory->id,
        ];

        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/products/' . $product->id, $payload);

        // 3. Проверка
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => 'Updated product',
                'price' => 550,
                'description' => 'Updated description',
                'img' => null,
                'category_id' => $drinkCategory->id,
            ],
        ]);

        $this->assertDatabaseHas('products', $payload);
    }

    public function test_regular_user_cannot_update_product(): void
    {
        //1. Подготовка данных
        $pizzaCategory = Category::factory()->pizza()->create();
        $drinkCategory = Category::factory()->drink()->create();

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $product = Product::factory()->create([
            'name' => 'Old product',
            'price' => 400,
            'description' => 'Old description',
            'img' => null,
            'category_id' => $pizzaCategory->id,
        ]);

        $payload = [
            'name' => 'Updated product',
            'price' => 550,
            'description' => 'Updated description',
            'img' => null,
            'category_id' => $drinkCategory->id,
        ];
        //2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/products/' . $product->id, $payload);
        //3. Проверка
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin only.'
        ]);
        $this->assertDatabaseMissing('products', $payload);
    }

    public function test_admin_can_delete_product(): void
    {
        // 1. Подготовка данных
        $pizzaCategory = Category::factory()->pizza()->create();
        $admin = User::factory()->admin()->create();
        $token = JWTAuth::fromUser($admin);

        $product = Product::factory()->create([
            'name' => 'Margarita test',
            'price' => 450,
            'description' => 'description pizza test',
            'img' => null,
            'category_id' => $pizzaCategory->id,

        ]);
        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/products/' . $product->id);
        // 3. Проверка
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Product deleted successfully',
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'name' => 'Margarita test',
        ]);
    }

    public function test_regular_user_cannot_delete_product(): void
    {
        // 1. Подготовка данных
        $pizzaCategory = Category::factory()->pizza()->create();
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $product = Product::factory()->create([
            'name' => 'Margarita test',
            'price' => 450,
            'description' => 'description pizza test',
            'img' => null,
            'category_id' => $pizzaCategory->id,
        ]);

        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/products/' . $product->id);

        // 3. Проверка
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin only.'
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Margarita test',
        ]);
    }
}
