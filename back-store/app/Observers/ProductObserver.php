<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product)
    {
        // Nếu stock thay đổi thì cập nhật auto_status
        if ($product->isDirty('stock')) {
            if ($product->stock <= 0) {
                $product->auto_status = 2; // hết hàng
            } else {
                $product->auto_status = 1; // còn hàng
            }
        }

        // status do admin chỉnh tay, không ghi đè
    }
}
