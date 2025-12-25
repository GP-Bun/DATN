<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'status',
        'is_featured',
        'category_id',
        'thumbnail',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 1)->orderBy('created_at', 'desc');
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            0 => 'Ẩn',
            1 => 'Còn hàng',
            2 => 'Hết hàng',
            default => 'Không rõ',
        };
    }

    // ✅ Trả về boolean hết hàng 
    public function getIsOutOfStockAttribute()
    {
        return $this->status == 2;
    }

    // ✅ Scope: chỉ lấy sản phẩm còn hàng 
    public function scopeAvailable($query)
    {
        return $query->where('status', 1);
    }

    // ✅ Scope: lấy tất cả sản phẩm hiển thị (còn hàng + hết hàng) 
    public function scopeVisible($query)
    {
        return $query->whereIn('status', [1, 2]);
    }

    // ✅ Scope: chỉ lấy sản phẩm hết hàng 
    public function scopeOutOfStock($query)
    {
        return $query->where('status', 2);
    }
}
