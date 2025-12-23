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
        'final_amount',
        'shipping_cost',
        'discount_amount',
        'payment_method',   // COD hoặc bank_transfer
        'transaction_id',   // Mã giao dịch từ Momo/ngân hàng
        'paid_at',          // Thời điểm thanh toán
        'is_verified',      // Đã xác minh giao dịch chưa
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'is_verified' => 'boolean',
        'final_amount' => 'float',
        'shipping_cost' => 'float',
        'discount_amount' => 'float',
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
     * Quan hệ: đơn hàng có thể gắn coupon
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Quan hệ: đơn hàng có nhiều thanh toán (payments)
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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

    /**
     * Helper: kiểm tra đơn hàng COD
     */
    public function isCod(): bool
    {
        return $this->payment_method === 'cod';
    }

    /**
     * Helper: kiểm tra đơn hàng chuyển khoản
     */
    public function isBankTransfer(): bool
    {
        return $this->payment_method === 'bank_transfer';
    }

    /**
     * Helper: kiểm tra giao dịch đã xác minh chưa
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Helper: tính tổng tiền sản phẩm (chưa tính phí ship, giảm giá)
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->quantity * $item->price);
    }

    public function couponRedemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function getOrderStatusLabelAttribute()
    {
        return [
            'pending'    => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped'    => 'Đã gửi hàng',
            'delivered'  => 'Đã giao',
            'cancelled'  => 'Đã hủy',
        ][$this->order_status] ?? $this->order_status;
    }

    public function getOrderStatusColorAttribute()
    {
        return [
            'pending'    => 'warning text-dark',
            'processing' => 'info text-dark',
            'shipped'    => 'primary',
            'delivered'  => 'success',
            'cancelled'  => 'danger',
        ][$this->order_status] ?? 'secondary';
    }

    public function getPaymentStatusLabelAttribute()
    {
        return [
            'unpaid'   => 'Chưa thanh toán',
            'paid'     => 'Đã thanh toán',
            'refunded' => 'Đã hoàn tiền',
        ][$this->payment_status] ?? $this->payment_status;
    }

    public function getPaymentStatusColorAttribute()
    {
        return [
            'unpaid'   => 'danger',
            'paid'     => 'success',
            'refunded' => 'warning text-dark',
        ][$this->payment_status] ?? 'secondary';
    }
}
