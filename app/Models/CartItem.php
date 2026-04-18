<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    /**
     * Строка корзины "принадлежит" пользователю.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Строка корзины "принадлежит" товару.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
