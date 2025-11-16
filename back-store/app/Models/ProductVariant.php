<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'color', 'size', 'stock', 'price', 'image', 'status'
    ];

    // Quan hệ với Product
    public function product() {
        return $this->belongsTo(Product::class);
    }
}
