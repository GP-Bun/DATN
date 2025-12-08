<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy 5 sản phẩm nổi bật, chọn những trường cần thiết
        $featuredProducts = Product::orderBy('price', 'desc')
            ->take(5)
            ->get(['id','name','price','thumbnail','reviews']);

        // Chuyển đổi đường dẫn ảnh thành URL đầy đủ
        $featuredProducts->transform(function ($product) {
            if ($product->thumbnail) {
                $product->thumbnail = url('storage/' . $product->thumbnail);
            }
            return $product;
        });

        // Lấy 3 danh mục nổi bật
        $featuredCategories = Category::take(3)
            ->get(['id','name']);

        // Lấy banner
        $featuredBanners = Banner::orderBy('created_at', 'desc')
            ->get(['id','image','link']);

        // Chuyển đổi đường dẫn ảnh banner thành URL đầy đủ
        $featuredBanners->transform(function ($banner) {
            if ($banner->image) {
                $banner->image = url('storage/' . $banner->image);
            }
            return $banner;
        });

        return response()->json([
            'banners' => $featuredBanners,
            'featured_products' => $featuredProducts,
            'featured_categories' => $featuredCategories,
        ]);
    }
}
