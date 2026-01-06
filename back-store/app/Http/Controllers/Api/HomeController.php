<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
// use App\Models\Banner;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy banner (Fallback if model doesn't exist yet)
        $banners = [];
        if (class_exists('App\Models\Banner')) {
            $banners = \App\Models\Banner::select('id', 'image', 'link')->get();
            $banners->transform(function ($banner) {
                if ($banner->image) {
                    $banner->image = url('storage/' . $banner->image);
                }
                return $banner;
            });
        } else {
            // Mock banners for better UI
            $banners = [
                [
                    'id' => 1,
                    'image' => 'https://bizweb.dktcdn.net/100/347/092/files/giay-sneaker-la-gi-1.jpg?v=1599104032003',
                    'link' => '/san-pham'
                ]
            ];
        }

        // Lấy 8 sản phẩm nổi bật (is_featured = 1), nếu không có thì lấy sản phẩm mới nhất
        $featuredProducts = Product::where('is_featured', 1)
            ->whereIn('status', [1, 2])
            ->with(['variants'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
        
        // Nếu không có sản phẩm nổi bật, lấy 8 sản phẩm mới nhất
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::whereIn('status', [1, 2])
                ->with(['variants'])
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->orderBy('created_at', 'desc')
                ->take(8)
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
