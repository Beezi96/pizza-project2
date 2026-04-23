<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'price',
        'quantity',
        'order_id',
        'product_id',
        'product_name',
        'product_img',
        'product_description',
    ];

    /**
     * Строка заказа "принадлежит" заказу.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Строка заказа "принадлежит" товару.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
