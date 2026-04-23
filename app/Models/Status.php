<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name'
    ];

    /**
     * Один статус может быть у многих заказов.
     * Пример: статус "new" -> много заказов в этом статусе
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
