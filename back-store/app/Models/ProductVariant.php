<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Color;
use App\Models\Size;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'original_price',
        'sale_price',
        'stock',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }
}
