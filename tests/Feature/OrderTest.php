<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_auth_user_can_create_order_from_cart(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();

        $products = Product::query()->take(2)->get();
        $firstProduct = $products[0];
        $secondProduct = $products[1];

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $secondProduct->id,
            'quantity' => 1,
        ]);

        $token = JWTAuth::fromUser($user);

        $payload = [
            'email' => 'user2@test.com',
            'phone' => '+799999999',
            'address' => 'test address',
            'delivery_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Order created',
            'data' => [
                'email' => 'user2@test.com',
                'phone' => '+799999999',
                'address' => 'test address',
                'user_id' => $user->id,
            ],
        ]);

        $this->assertDatabaseHas('orders', [
            'email' => 'user2@test.com',
            'phone' => '+799999999',
            'address' => 'test address',
            'user_id' => $user->id,

        ]);

        $order = Order::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($order);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
            'product_name' => $firstProduct->name,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $secondProduct->id,
            'quantity' => 1,
            'product_name' => $secondProduct->name,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_id' => $secondProduct->id,
        ]);

    }

    public function test_auth_user_cannot_create_order_from_empty_cart(): void
    {
        $this->seed();

        $user = User::query()->where('is_admin', false)->first();
        $token = JWTAuth::fromUser($user);

        $payload = [
            'email' => 'user2@test.com',
            'phone' => '+799999999',
            'address' => 'test address',
            'delivery_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', $payload);

        $response->assertStatus(422);

        $response->assertJson([
            'message' => 'Cart is empty',
        ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }
}
