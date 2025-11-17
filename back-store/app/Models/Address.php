<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Address - Địa chỉ giao hàng
 * Lưu thông tin địa chỉ của người dùng
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'address_line', 'city', 'province', 'postal_code', 'country', 'is_default',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
