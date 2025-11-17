<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model OrderItem - Chi tiết sản phẩm trong đơn hàng
 * Mỗi OrderItem = 1 dòng sản phẩm với số lượng và giá
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'variant_id', 'quantity', 'price'];

    protected $casts = ['price' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
