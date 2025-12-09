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
        'user_id', 'recipient_name', 'phone',
        'street', 'city', 'district', 'province', 'is_default'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // lấy địa chỉ mặc định của user
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }
}
