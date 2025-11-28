<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;

class ProductVariantController extends Controller
{
    public function destroy($productId, $variantId)
    {
        $variant = ProductVariant::where('product_id', $productId)->findOrFail($variantId);
        $variant->delete();

        return redirect()->route('admin.products.index')->with('success', 'Xóa biến thể thành công!');
    }
}
