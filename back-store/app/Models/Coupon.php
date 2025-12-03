<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'starts_at',
        'ends_at',
        'usage_limit',
        'used_count',
        'active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'boolean',
    ];

    // Kiểm tra voucher còn hiệu lực
    public function isValid($orderAmount = null): bool
    {
        if (!$this->active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        if ($orderAmount && $this->min_order_amount && $orderAmount < $this->min_order_amount) return false;

        return true;
    }

    // Tính số tiền giảm
    public function calculateDiscount($orderAmount): float
    {
        if ($this->type === 'percent') {
            $discount = $orderAmount * ($this->value / 100);
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }
            return $discount;
        }

        if ($this->type === 'fixed') {
            return min($this->value, $orderAmount);
        }

        return 0;
    }
}
