<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_name',
        'quantity',
        'price',
        'total'
    ];

    /**
     * Quan hệ: OrderItem thuộc về một Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Quan hệ: OrderItem liên kết với Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Quan hệ: OrderItem liên kết với ProductVariant
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Helper: tính tổng tiền cho item
     */
    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }
}
