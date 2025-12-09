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

        // Lấy sản phẩm nổi bật (ví dụ: lấy 8 sản phẩm mới nhất)
        $featuredProducts = Product::select('id', 'name', 'price', 'thumbnail')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

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
