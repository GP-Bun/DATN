<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;

class ProductVariantController extends Controller
{
    public function destroy($productId, $variantId)
    {
        $variant = ProductVariant::where('product_id', $productId)
                                 ->where('id', $variantId)
                                 ->firstOrFail();

        $variant->delete();

        return redirect()
            ->route('admin.products.index', $productId)
            ->with('success', 'Xóa biến thể thành công!');
    }
}
