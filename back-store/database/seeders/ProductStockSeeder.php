<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\Color;
use Illuminate\Support\Str;

class ProductStockSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo category trước (nếu chưa có)
        // $category = \App\Models\Category::create([
        //     'name' => 'Giày',
        //     'slug' => Str::slug('Giày'),
        // ]);

        // Tạo một sản phẩm mới
        $product = Product::create([
            'category_id' => 1, // id category có sẵn
            'name' => 'Giày bạc thử nghiệm',
            'slug' => Str::slug('Giày bạc thử nghiệm'),
            'price' => 500000,
            'stock' => 10,
            'status' => 1,
        ]);

        // Tạo size và color
        $size38 = Size::firstOrCreate(['value' => 38]);
        $size39 = Size::firstOrCreate(['value' => 39]);
        $colorBac = Color::firstOrCreate(['name' => 'Bạc', 'code' => '#C0C0C0']);

        // Tạo các biến thể cho sản phẩm
        ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => $size38->id,
            'color_id' => $colorBac->id,
            'stock' => 5,
            'original_price' => 500000,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => $size39->id,
            'color_id' => $colorBac->id,
            'stock' => 0, // hết hàng
            'original_price' => 500000,
        ]);

        echo "Seeder chạy xong. Kiểm tra trạng thái sản phẩm và biến thể trong DB.\n";
    }
}
