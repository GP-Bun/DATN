<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Order - Đơn hàng
 * Lưu thông tin đơn hàng của người dùng
 */
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'address_id',
        'order_status',   
        'payment_status', 
        'total_amount',
    ];

    /**
     * Quan hệ: đơn hàng thuộc về một user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ: đơn hàng có một địa chỉ giao hàng
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Quan hệ: đơn hàng có nhiều sản phẩm (order_items)
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope: lấy đơn hàng theo trạng thái
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('order_status', $status);
    }

    /**
     * Helper: kiểm tra đơn hàng đã thanh toán chưa
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
