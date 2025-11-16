<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Danh sách sản phẩm
    public function index()
    {
        $products = Product::with('category')->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    // Form thêm sản phẩm
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // Lưu sản phẩm mới
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'category_id'=>'required|exists:categories,id',
            'description'=>'nullable|string',
            'price'=>'required|numeric|min:0',
            'status'=>'required|in:ACTIVE,INACTIVE,OUT_OF_STOCK',
            'image'=>'nullable|image|max:2048',
            'variants'=>'nullable|array',
            'variants.*.color'=>'required_with:variants|string|max:50',
            'variants.*.size'=>'required_with:variants|string|max:50',
            'variants.*.stock'=>'required_with:variants|integer|min:0',
            'variants.*.price'=>'required_with:variants|numeric|min:0',
        ]);

        $data = $request->only(['name','category_id','description','price','status']);
        if($request->hasFile('image')){
            $data['image'] = $request->file('image')->store('products','public');
        }

        // Tạo sản phẩm
        $product = Product::create($data);

        // Lưu biến thể
        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                $product->variants()->create($variant);
            }
        }

        return redirect()->route('admin.products.index')->with('success','Product created.');
    }

    // Form sửa sản phẩm
    public function edit(Product $product)
    {
        $categories = Category::all();
        // Load variants để hiển thị trong form
        $product->load('variants');
        return view('admin.products.edit', compact('product','categories'));
    }

    // Cập nhật sản phẩm
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'category_id'=>'required|exists:categories,id',
            'description'=>'nullable|string',
            'price'=>'required|numeric|min:0',
            'status'=>'required|in:ACTIVE,INACTIVE,OUT_OF_STOCK',
            'image'=>'nullable|image|max:2048',
            'variants'=>'nullable|array',
            'variants.*.color'=>'required_with:variants|string|max:50',
            'variants.*.size'=>'required_with:variants|string|max:50',
            'variants.*.stock'=>'required_with:variants|integer|min:0',
            'variants.*.price'=>'required_with:variants|numeric|min:0',
        ]);

        $data = $request->only(['name','category_id','description','price','status']);
        if($request->hasFile('image')){
            if($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products','public');
        }

        $product->update($data);

        // Xóa hết biến thể cũ
        $product->variants()->delete();

        // Lưu biến thể mới
        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                $product->variants()->create($variant);
            }
        }

        return redirect()->route('admin.products.index')->with('success','Product updated.');
    }

    // Xóa sản phẩm
    public function destroy(Product $product)
    {
        if($product->image) Storage::disk('public')->delete($product->image);
        // Xóa cả biến thể trước
        $product->variants()->delete();
        $product->delete();
        return redirect()->route('admin.products.index')->with('success','Product deleted.');
    }
}
