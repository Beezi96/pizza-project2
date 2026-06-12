<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;

class CartService
{
    private const PIZZA_LIMIT = 10;
    private const DRINK_LIMIT = 20;

    private function findUserCartItem($userId, $productId)
    {
        return CartItem::query()
            ->where('user_id', $userId) // Берем записи только этого пользователя
            ->where('product_id', $productId) // Из них берем только записи по наружному товару
            ->first(); // Возращаем первую найденную запись
    }

    private function validateCartLimits($userId, $targetProduct, $targetQuantity)
    {
        // Шаг 1. Загружаем корзину пользователя
        $cartItems = CartItem::query()
            ->with('product.category') // подгружаем товары и категории товара
            ->where('user_id', $userId)
            ->get(); // берем все записи корзины пользователя

        // Шаг 2. Создаем счетчики
        $pizzaCount = 0; // Сколько всего пицц булет в корзине
        $drinkCount = 0; // Сколько всего напитков будет в корзине
        $targetProductExists = false; // Есть ли уже в корзине именно тот товар, который мы сейчас меняем

        // Шаг 3. Проходим по всем товарам в корзине
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
        // добавляем его кол-во к нужной категории
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

    public function getUserCartItems($userId)
    {
        // Здесь лежит запрос к БД
        return CartItem::query()
            ->with(['product.category']) // подгружаем товары и категории
            ->where('user_id', $userId)
            ->get();
    }

    public function addProductToCart($userId, $productId, $quantity)
    {
        // 1. Ищем товар, который хотят добавить
        $product = Product::query()
            ->with('category')
            ->find($productId);

        // 2. Проверяем такой товар в корзине
        $item = $this->findUserCartItem($userId, $product->id);

        // 3. Считаем новое кол-во
        // Если товара еще нет в корзине, берем кол-во из запроса
        $newQuantity = $quantity;

        // Если товар уже был в корзине, прибовляем новое кол-во к старому
        if ($item) {
            $newQuantity = $item->quantity + $quantity;
        }

        // 4. Проверяем лимиты корзины
        $limitError = $this->validateCartLimits($userId, $product, $newQuantity);

        if ($limitError) {
            return $limitError;
        }


        if (!$item) {
            $item = new CartItem();
            $item->user_id = $userId;
            $item->product_id = $product->id;
        }

        $item->quantity = $newQuantity;
        $item->save();

        return response()->json([
            'data' => $item->load('product.category'),
        ], 201);
    }
}
