<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Константы лимитов
    private const PIZZA_LIMIT = 10;
    private const DRINK_LIMIT = 20;

    // Поиск у пользователя конкрутный товар в корзине
    private function findUserCartItem($userId, $productId)
    {
        return CartItem::query()
            ->where('user_id', $userId) // Берем записи только этого пользователя
            ->where('product_id', $productId) // Из них берем только записи по наружному товару
            ->first(); // Возращаем первую найденную запись
    }

    // Если у пользователя станет таоке кол-во товаров, не привысит ли корзина лимиты?
    private function validateCartLimits($userId, $targetProduct, $targetQuantity)
    {
        // Шаг 1. Загружаем корзину
        $cartItems = CartItem::query()
            ->with('product.category') // подгружаем товары и категории товара
            ->where('user_id', $userId)
            ->get(); // берем все записи корзины пользователя

        // Шаг 2. Создаем счетчики
        $pizzaCount = 0; // Сколько всего пицц булет в корзине
        $drinkCount = 0; // Сколько всего напитков будет в корзине
        $targetProductExists = false; // Есть ли уже в корзине именно тот товар, который мы сейчас меняем

        // Шаг 3. Проходим по корзине
        foreach ($cartItems as $cartItem) { // берем каждую запись корзины
            // Шаг 4. Берем текущее кол-во
            $quantity = $cartItem->quantity;
            // Шаг 5. Если это тот самый товар
            if ($cartItem->product_id === $targetProduct->id) { // Если в корзине уже есть тот товар, который мы сейчас: добавляем, или обновляем, то вместо старого кол-во надо подставить новое
                $quantity = $targetQuantity;
                $targetProductExists = true;
            }

            // Шаг 6. Получаем категорию
            $categoryCode = $cartItem->product->category->code;

            // Шаг 7. Суммируем по категории
            if ($categoryCode === 'pizza') {
                $pizzaCount += $quantity;
            }
            if ($categoryCode === 'drink') {
                $drinkCount += $quantity;
            }
        }
        // Шаг 8. Если товар еще не было в корзине
        if ($targetProductExists) {
            if ($targetProduct->category->code === 'pizza') {
                $pizzaCount += $targetQuantity;
            }
            if ($targetProduct->category->code === 'drink') {
                $drinkCount += $targetQuantity;
            }
        }

        // Шаг 9. Проверям лимиты
        if ($pizzaCount > self::PIZZA_LIMIT) {
            return response()->json([
                'message' => 'Pizza limit 10.',
            ], 422);
        }

        if ($drinkCount > self::DRINK_LIMIT) {
            return response()->json([
                'message' => 'Drink limit 20.',
            ], 422);
        }

        // Шаг 10. Есил все хорошо: ошибок нет, лимиты не нарушены, можно продолжать выполнение
        return null;


    }

    // Показать корзину пользователя
    public function index(Request $request)
    {
        $user = auth()->user();
        // Загружаем все его позичии козины
        $items = CartItem::query()
            ->with(['product.category']) // подгружаем товары и категории
            ->where('user_id', $user->id)
            ->get();

        // возращаем JSON
        return response()->json([
            'message' => 'Users cart',
            'data' => $items,
        ]);
    }

    // Добавить товар в корзину
    public function store(Request $request)
    {
        $user = auth()->user();

        // Валидация входных данных
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);


        // Ищем товар
        $product = Product::query()
            ->with('category')
            ->find($data['product_id']);

        // Ищет товар в корзине
        $item = $this->findUserCartItem($user->id, $product->id);

        $newQuantity = $data['quantity'];

        // Если товар есть, возьми его старое количество
        if ($item) {
            $newQuantity = $item->quantity + $data['quantity'];
        }

        $limitError = $this->validateCartLimits($user->id, $product, $newQuantity);

        if ($limitError) {
            return $limitError;
        }


        if (!$item) {
            $item = new CartItem();
            $item->user_id = $user->id;
            $item->product_id = $product->id;
        }

        $item->quantity = $newQuantity;
        $item->save();

        return response()->json([
            'data' => $item->load('product.category'),
        ], 201);
    }

    public function update(Request $request, $product_id)
    {
        $user = auth()->user();

        // Валидируем новое quantity
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
        // Находим товар по product_id
        $product = Product::query()
            ->with('category')
            ->find($product_id);
        // Ищем этот товар в корзине пользователя
        $item = $this->findUserCartItem($user->id, $product->id);
        // Если нет - 404
        if (!$item) {
            return response()->json([
                'message' => 'Cart not found.',
            ], 404);
        }
        // Проверяем лимиты
        $limitError = $this->validateCartLimits($user->id, $product, $data['quantity']);
        if ($limitError) {
            return $limitError;
        }
        // Обновляем кол-во
        $item->quantity = $data['quantity'];
        $item->save();

        return response()->json([
            'data' => $item->load('product.category'),
        ]);

    }

    public function destroy(Request $request, $product_id)
    {
        $user = auth()->user();

        // Ищем товар в корзине пользователя по product_id
        $item = $this->findUserCartItem($user->id, $product_id);
        // Если нет - 404
        if (!$item) {
            return response()->json([
                'message' => 'Cart item not found.',
            ], 404);
        }

        // Если есть - удаляем
        $item->delete();
        return response()->json([
            'message' => 'Deleted',
        ]);
    }
}
