<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        Review::truncate(); // Xoá dữ liệu cũ nếu cần

        $reviews = [
            [
                'product_id' => 1,
                'user_id' => 1,
                'user_name' => 'Nguyen Van A',
                'user_email' => 'a@example.com',
                'rating' => 5,
                'comment' => 'Sản phẩm rất tốt, giao hàng nhanh!',
                'status' => 1,
            ],
            [
                'product_id' => 1,
                'user_id' => 2,
                'user_name' => 'Tran Thi B',
                'user_email' => 'b@example.com',
                'rating' => 4,
                'comment' => 'Chất lượng ổn, nhưng đóng gói chưa chắc chắn.',
                'status' => 1,
            ],
            [
                'product_id' => 2,
                'user_id' => null,
                'user_name' => 'Khách Ẩn Danh',
                'user_email' => null,
                'rating' => 3,
                'comment' => 'Sản phẩm dùng tạm được, chưa như kỳ vọng.',
                'status' => 1,
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}