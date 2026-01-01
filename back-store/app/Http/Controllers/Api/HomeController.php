<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy banner
        $banners = Banner::select('id', 'image', 'link')->get();
        
        // Chuyển đổi đường dẫn ảnh banner thành URL đầy đủ
        $banners->transform(function ($banner) {
            if ($banner->image) {
                $banner->image = url('storage/' . $banner->image);
            }
            return $banner;
        });

        // Lấy 8 sản phẩm nổi bật (is_featured = 1), nếu không có thì lấy sản phẩm mới nhất
        $featuredProducts = Product::where('is_featured', 1)
            ->whereIn('status', [1, 2]) // Lấy cả sản phẩm còn hàng và hết hàng
            ->with(['variants'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->select('id', 'name', 'price', 'thumbnail', 'stock')
            ->get();
        
        // Nếu không có sản phẩm nổi bật, lấy 8 sản phẩm mới nhất
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::whereIn('status', [1, 2])
                ->with(['variants'])
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->select('id', 'name', 'price', 'thumbnail', 'stock')
                ->get();
        }
        
        // Chuyển đổi đường dẫn ảnh thành URL đầy đủ
        $featuredProducts->transform(function ($product) {
            if ($product->thumbnail) {
                // Nếu thumbnail chưa có http thì thêm prefix
                if (!str_starts_with($product->thumbnail, 'http')) {
                    $product->thumbnail = url('storage/' . $product->thumbnail);
                }
            }
            return $product;
        });

        // Lấy danh mục nổi bật (ví dụ: 6 danh mục đầu tiên)
        $featuredCategories = Category::select('id', 'name')
            ->take(6)
            ->get();

        return response()->json([
            'banners' => $banners,
            'featured_products' => $featuredProducts,
            'featured_categories' => $featuredCategories,
        ]);
    }
}
