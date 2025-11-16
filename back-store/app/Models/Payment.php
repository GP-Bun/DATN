<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Payment - Thông tin thanh toán
 * Lưu thông tin thanh toán cho mỗi đơn hàng
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'provider', 'amount', 'status', 'transaction_code'];

    protected $casts = ['amount' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
