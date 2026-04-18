<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Status;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Показ список заказов текущего пользователя
    public function index()
    {
        // Проверка пользователя
        $user = auth()->user();

        $orders = Order::query()
            ->with(['status', 'items'])
            ->where('user_id', $user->id)
            ->latest() // Сортировка по новым сверху
            ->get();

        return response()->json([
            'data' => $orders,
        ]);

    }

    // Показать один заказ текущего пользователя
    public function show($id)
    {
        $user = auth()->user();

        $order = Order::query()
            ->with(['status', 'items'])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'data' => $order,
        ]);
    }

    //Оформить заказ из корзины текущего пользователя
    public function store(Request $request)
    {
        // Получаем пользователя, после проверки токена
        $user = auth()->user();

        // Валидация заказа
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'delivery_time' => ['required', 'date'],
        ]);

        // Получаем корзину пользователя (берем все товары из корзины теущего пользователя)
        $cartItems = CartItem::query()
            ->with(['product'])
            ->where('user_id', $user->id)
            ->get();

        // Проверяем, что корзина не пуста (нельзя оформить заказ из пустой корзины)
        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty',
            ], 422);
        }

        // Находим статус new
        $status = Status::query()
            ->where('code', 'new')
            ->first();

        if (!$status) {
            return response()->json([
                'message' => 'Status "new" not found',
            ], 500);
        }

        // Сохдаем заказ (создается запись в таблице orders
        $order = Order::query()->create([
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'delivery_time' => $data['delivery_time'],
            'user_id' => $user->id,
            'status_id' => $status->id
        ]);

        // Создаем order_items
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;

            OrderItem::query()->create([
                'price' => $product->price,
                'quantity' => $cartItem->quantity,
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_img' => $product->img ?? '',
                'product_description' => $product->description,
            ]);
        }

        // Очищаем корзину
        CartItem::query()
            ->where('user_id', $user->id)
            ->delete();

        // Подгружаем связи
        $order->load(['status', 'items']);

        return response()->json([
            'message' => 'Order created',
            'data' => $order,
        ], 201);
    }
}
