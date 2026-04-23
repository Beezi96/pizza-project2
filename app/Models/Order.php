<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'email',
        'phone',
        'address',
        'delivery_time',
        'user_id',
        'status_id',
    ];

    /**
     * Заказ "принадлежит" пользователю (кто оформил).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Заказ "принадлежит" статусу.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Заказ "имеет много" строк заказа (order_items).
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
