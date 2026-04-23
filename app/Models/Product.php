<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'description',
        'img',
        'category_id',
    ];

    /**
     * Товар "принадлежит" категории.
     * Пример: "Пепперони" -> принадлежит "pizza"
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Товар может встречаться во многих строках заказов.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Товар может быть во многих корзинах (у разных пользователей).
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
