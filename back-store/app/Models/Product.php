<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','slug','description','price','status','category_id','thumbnail','images'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function variants(){
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class)->where('status', 1)->orderBy('created_at', 'desc');
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            0 => 'Ẩn',
            1 => 'Còn hàng',
            2 => 'Hết hàng',
            default => 'Không rõ',
        };
    }
}
