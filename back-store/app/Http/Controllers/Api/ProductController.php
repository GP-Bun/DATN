<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 🟦 Lấy danh sách sản phẩm
    public function index(Request $request)
    {
        $products = Product::with(['category'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($products);
    }

    // 🟩 Lấy chi tiết 1 sản phẩm kèm biến thể, màu, size
    public function show($id)
    {
        $product = Product::with([
            'variants.color',
            'variants.size',
            'category'
        ])->findOrFail($id);

        // 🎨 Danh sách màu không trùng (unique)
        $colors = $product->variants
            ->pluck('color')
            ->unique('id')
            ->values()
            ->map(function ($color) {
                if (!$color) return null;
                return [
                    'id' => $color->id,
                    'name' => $color->name
                ];
            })->filter()->values();

        // 📏 Danh sách size không trùng (unique)
        $sizes = $product->variants
            ->pluck('size')
            ->unique('id')
            ->values()
            ->map(function ($size) {
                if (!$size) return null;
                return [
                    'id' => $size->id,
                    'name' => $size->name
                ];
            })->filter()->values();

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $product->price,

            // 🟨 Fix lỗi: đổi thumbnail → image
            'image' => $product->image,
            'images' => $product->images,

            'category' => $product->category,

            // Trả ra 2 danh sách gọn gàng cho UI
            'colors' => $colors,
            'sizes' => $sizes,

            // Variants chi tiết để FE tìm variant_id khi chọn màu + size
            'variants' => $product->variants->map(function ($v) {
                return [
                    'id' => $v->id,
                    'color_id' => $v->color_id,
                    'size_id' => $v->size_id,
                    'original_price' => $v->original_price,
                    'sale_price' => $v->sale_price,
                    'stock' => $v->stock,
                    'status' => $v->status,
                ];
            }),
        ]);
    }
}
