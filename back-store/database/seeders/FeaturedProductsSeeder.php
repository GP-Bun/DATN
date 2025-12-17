<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class FeaturedProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset tất cả sản phẩm về không nổi bật
        Product::query()->update(['is_featured' => false]);
        
        // Lấy 8 sản phẩm đầu tiên (hoặc tất cả nếu ít hơn 8) và đánh dấu là nổi bật
        $products = Product::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
        
        if ($products->count() > 0) {
            foreach ($products as $product) {
                $product->update(['is_featured' => true]);
            }
            
            echo "Đã đánh dấu {$products->count()} sản phẩm là nổi bật.\n";
        } else {
            echo "Không có sản phẩm nào để đánh dấu nổi bật.\n";
        }
    }
}
