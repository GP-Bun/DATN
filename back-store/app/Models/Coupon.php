<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'usage_limit', 'used_count', 'min_order_amount', 'starts_at', 'ends_at', 'active'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'used_count' => 'integer',
        'usage_limit' => 'integer',
        'min_order_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'boolean',
    ];

    // Kiểm tra coupon có hợp lệ để áp dụng không
    public function isValid()
    {
        if (! $this->active) return false;

        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;

        return true;
    }

    // Áp dụng coupon lên 1 số tiền và trả về số tiền giảm
    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'percent') {
            return round(($this->value / 100) * $amount, 2);
        }

        return (float) $this->value;
    }
}
