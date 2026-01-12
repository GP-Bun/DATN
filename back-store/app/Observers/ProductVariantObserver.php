<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Models\Product;

class ProductVariantObserver
{
    public function saving(ProductVariant $variant)
    {
        // Nếu admin sửa tay status của biến thể thì giữ nguyên
        if ($variant->isDirty('status')) {
            return;
        }

        // Nếu stock thay đổi thì tự động cập nhật trạng thái biến thể
        if ($variant->isDirty('stock')) {
            if ($variant->stock <= 0) {
                $variant->status = 2; // hết hàng
            } else {
                $variant->status = 1; // còn hàng
            }
        }
    }

    public function saved(ProductVariant $variant)
    {
        $product = $variant->product;

        // Nếu stock thay đổi thì cập nhật auto_status của sản phẩm cha
        if ($product->variants()->where('stock', '>', 0)->exists()) {
            $product->auto_status = 1; // còn hàng
        } else {
            $product->auto_status = 2; // hết hàng
        }

        $product->saveQuietly(); // tránh vòng lặp Observer
    }
}
