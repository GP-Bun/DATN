<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Size;


class ProductController extends Controller
{
    // 🟦 Lấy danh sách sản phẩm
    public function index(Request $request)
    {
        $products = Product::with(['category', 'variants.color', 'variants.size'])
            ->visible()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Chuyển đổi đường dẫn ảnh thành URL đầy đủ
        $products->getCollection()->transform(function ($product) {
            if ($product->thumbnail) {
                $product->thumbnail_url = url('storage/' . $product->thumbnail);
                $product->thumbnail = url('storage/' . $product->thumbnail);
            }
            if ($product->images && is_array($product->images)) {
                $product->images = array_map(function ($img) {
                    return url('storage/' . $img);
                }, $product->images);
            }

            return $product;
        });

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

        // // 📏 Danh sách size không trùng (unique)
        // $sizes = $product->variants
        //     ->pluck('size')
        //     ->unique('id')
        //     ->values()
        //     ->map(function ($size) {
        //         if (!$size) return null;
        //         return [
        //             'id' => $size->id,
        //             'value' => $size->value, // Giá trị size thực tế (22, 23, 24...)
        //             'name' => (string)$size->value // Để tương thích với frontend
        //         ];
        //     })->filter()->values();
        // 📏 Danh sách size chuẩn + stock
        $allSizes = Size::all();

        $sizes = $allSizes->map(function ($size) use ($product) {
            $variant = $product->variants->firstWhere('size_id', $size->id);
            return [
                'id' => $size->id,
                'value' => $size->value,
                'name' => (string)$size->value,
                'stock' => $variant ? $variant->stock : 0
            ];
        });


        // Chuyển đổi đường dẫn ảnh thành URL đầy đủ
        $thumbnailUrl = $product->thumbnail ? url('storage/' . $product->thumbnail) : null;
        $imagesUrls = [];
        if ($product->images && is_array($product->images)) {
            $imagesUrls = array_map(function ($img) {
                return url('storage/' . $img);
            }, $product->images);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,

            // Ảnh đại diện - trả về cả đường dẫn gốc và URL đầy đủ
            'thumbnail' => $thumbnailUrl,
            'image' => $thumbnailUrl, // Tương thích với code cũ
            'images' => $imagesUrls,

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
