<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Admin có tất cả quyền
    public function hasPermission(string $permission): bool
    {
        return true; // Admin có full quyền
    }

    // Lấy loại tài khoản
    public function getAccountType(): string
    {
        return 'admin';
    }
}
