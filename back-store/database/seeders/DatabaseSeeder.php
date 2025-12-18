<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         \App\Models\Category::factory(5)->create();

         $this->call(ColorSizeSeeder::class);
        // Gọi OrderSeeder để tạo dữ liệu mẫu
        $this->call(OrderSeeder::class);

        // Gọi CouponSeeder để tạo mã giảm giá
        $this->call(CouponSeeder::class);

        

        $this->call([
            ProductStockSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
