<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
