<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo vài mã giảm giá mẫu
        Coupon::create([
            'code' => 'SUMMER100',
            'type' => 'percent',
            'value' => 10,
            'usage_limit' => 100,
            'used_count' => 0,
            'min_order_amount' => 100000,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(30),
            'active' => true,
        ]);

        Coupon::create([
            'code' => 'WELCOME50K0',
            'type' => 'fixed',
            'value' => 50000,
            'usage_limit' => 50,
            'used_count' => 0,
            'min_order_amount' => 200000,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(60),
            'active' => true,
        ]);

        Coupon::create([
            'code' => 'VIPFREE1',
            'type' => 'percent',
            'value' => 15,
            'usage_limit' => null,
            'used_count' => 0,
            'min_order_amount' => 500000,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(1),
            'active' => true,
        ]);

      
    }
}
