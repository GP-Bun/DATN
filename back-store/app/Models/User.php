<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'active',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];


    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Tổng tiền giỏ hàng của user
    public function getCartTotalAttribute()
    {
        return $this->cart
            ? $this->cart->items->sum(fn($item) => $item->price * $item->quantity)
            : 0;
    }

    protected static function booted()
    {
        static::deleting(function ($user) {
            if ($user->cart) {
                $user->cart->items()->delete();
                $user->cart()->delete();
            }
        });
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        // Nếu bạn lưu quyền trong cột JSON 'permissions'
        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions);
    }

}
