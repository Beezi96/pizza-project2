<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Пользователь имеет много заказов
    public function orders(): HasMany
    {
        // Пользователь сделал много заказов
        return $this->hasMany(Order::class);
    }

    // Пользователь имеет много товаров в корзине
    public function cartItems(): HasMany
    {
        // У пользователя много строк корзины
        return $this->hasMany(CartItem::class);
    }

    // Возращаем значение, которое будет записано в поле "sub" внутри JWT.
    // Обычно это id пользователя.
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Возращает дополнительные данны, которые можно положить в JWT.
    // Пока нам ничего дополнительного не нужно.
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
