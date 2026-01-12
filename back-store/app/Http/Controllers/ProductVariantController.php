<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    // Thêm biến thể mới cho sản phẩm
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'color' => 'required|string|max:100',
            'size' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'color' => $request->color,
            'size' => $request->size,
            'price' => $request->price,
            'stock' => $request->stock,
            'status' => $request->status ?? 'ACTIVE',
        ]);

        return redirect()->back()->with('success', 'Thêm biến thể thành công!');
    }

    // Cập nhật biến thể
    public function update(Request $request, ProductVariant $variant)
    {
        $request->validate([
            'color' => 'required|string|max:100',
            'size' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $variant->update([
            'color' => $request->color,
            'size' => $request->size,
            'price' => $request->price,
            'stock' => $request->stock,
            'status' => $request->status ?? $variant->status,
        ]);

        return redirect()->back()->with('success', 'Cập nhật biến thể thành công!');
    }

    // Xóa biến thể
    public function destroy(ProductVariant $variant)
    {
        $variant->delete();

        return redirect()->back()->with('success', 'Xóa biến thể thành công!');
    }
}
