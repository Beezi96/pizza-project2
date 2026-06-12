<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class OrderFactoryTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_auth_user_can_crete_order_form_cart(): void
    {
        // 1. Подготовка данных
        Status::factory()->news()->create();

        $user = User::factory()->create();
        $category = Category::factory()->pizza()->create();

        $firstProduct = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 400,
        ]);

        $secondProduct = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 500,
        ]);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
        ]);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $secondProduct->id,
            'quantity' => 1,
        ]);

        $token = JWTAuth::fromUser($user);

        $payload = [
            'email' => 'user-test@test.com',
            'phone' => '+799999999',
            'address' => 'address-test',
            'delivery_time' => now()->addHour(2)->format('Y-m-d H:i:s'),
        ];


        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer '. $token)
            ->postJson('/api/orders', $payload);

        // 3. Проверка
        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Order created',
            'data' => [
                'email' => 'user-test@test.com',
                'phone' => '+799999999',
                'address' => 'address-test',
                'user_id' => $user->id,
            ],
        ]);

        $this->assertDatabaseHas('orders', [
            'email' => 'user-test@test.com',
            'phone' => '+799999999',
            'address' => 'address-test',
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
    }

    public function test_auth_user_cannot_create_order_from_empty_cart(): void
    {
        // 1. Подготовка данных
        Status::factory()->news()->create();
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $payload = [
            'email' => 'user-test@test.com',
            'phone' => '+799999999',
            'address' => 'address-test',
            'delivery_time' => now()->addHour(2)->format('Y-m-d H:i:s'),
        ];

        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', $payload);

        // 3. Проверка
        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Cart is empty',
        ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_auth_user_can_only_view_own_orders(): void
    {
        // 1. Подготовка данных
        $user = User::factory()->create();
        $status = Status::factory()->news()->create();

        $category = Category::factory()->pizza()->create();

        $firstOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
        ]);


        $secondOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        OrderItem::factory()->create([
            'order_id' => $firstOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        OrderItem::factory()->create([
            'order_id' => $secondOrder->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $token = JWTAuth::fromUser($user);

        // 2. Действие
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');

    }

    public function test_guest_cannot_get_order_list(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }

    public function test_auth_user_can_view_his_order_by_id(): void
    {
        $user = User::factory()->create();
        $status = Status::factory()->news()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $order->id,
                'user_id' => $user->id,
            ],
        ]);
    }

    public function test_auth_user_cannot_view_other_user_order(): void
    {
        $currentUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = Status::factory()->news()->create();

        $otherUserOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status_id' => $status->id,
        ]);

        $token = JWTAuth::fromUser($currentUser);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders/' . $otherUserOrder->id);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Order not found',
        ]);
    }

    public function test_admin_can_view_all_order_list(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $status = Status::factory()->news()->create();

        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_regular_user_cannot_view__admin_order_list(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/orders');

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin only.',
        ]);
    }

    public function test_admin_can_view_order_by_id(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $status = Status::factory()->news()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/orders/' . $order->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $order->id,
                'user_id' => $user->id,
            ],
        ]);
    }

    public function test_regular_user_cannot_view_admin_order_by_id(): void
    {
        $user = User::factory()->create();
        $status = Status::factory()->news()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/orders/' . $order->id);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin only.',
        ]);
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $newStatus = Status::factory()->create([
            'name' => 'Новый',
            'code' => 'new',
        ]);

        $cookingStatus = Status::factory()->create([
            'name' => 'Готовится',
            'code' => 'cooking',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status_id' => $newStatus->id,
        ]);

        $token = JWTAuth::fromUser($admin);

        $payload = [
            'status_id' => $cookingStatus->id,
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/admin/orders/' . $order->id . '/status', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Order status updated successfully',
            'data' => [
                'id' => $order->id,
                'status_id' => $cookingStatus->id,
            ],
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_id' => $cookingStatus->id,
        ]);

    }

    public function test_regular_user_cannot_update_order_status(): void
    {
        $user = User::factory()->create();
        $newStatus = Status::factory()->create([
            'name' => 'Новый',
            'code' => 'new',
        ]);
        $cookingStatus = Status::factory()->create([
            'name' => 'Готовится',
            'code' => 'cooking',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status_id' => $newStatus->id
        ]);

        $token = JWTAuth::fromUser($user);
        $payload = [
            'status_id' => $cookingStatus->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/admin/orders/' . $order->id . '/status', $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin only.',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_id' => $newStatus->id,
        ]);

    }
}
