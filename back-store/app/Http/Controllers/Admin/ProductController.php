<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;

class ProductController extends Controller
{
    // ===== DANH SÁCH =====
    public function index()
    {
        $products = Product::with('category')->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    // ===== FORM THÊM =====
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // ===== LƯU SẢN PHẨM =====
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:0,1,2',
            'thumbnail' => 'required|image|max:2048',
            'images.*' => 'nullable|image|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.color' => 'required|string|max:50',
            'variants.*.sizes' => 'required|array|min:1',
            'variants.*.original_price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
        ]);

        $slug = Str::slug($request->name);

        // Kiểm tra slug đã tồn tại chưa
        if (Product::withTrashed()->where('slug', $slug)->exists()) {
            return back()
                ->withErrors(['name' => 'Tên sản phẩm đã tồn tại.'])
                ->withInput();
        }

        // xử lý thumbnail, images...
        $thumbnailPath = $request->file('thumbnail')->store('products', 'public');
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $imagePaths[] = $img->store('products', 'public');
            }
        }

        $firstVariant = collect($request->variants)->first();

        $product = Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $firstVariant['original_price'] ?? 0,
            'status' => $request->status,
            'category_id' => $request->category_id,
            'thumbnail' => $thumbnailPath,
            'images' => $imagePaths,
        ]);

        foreach ($request->variants as $variant) {
            foreach ($variant['sizes'] as $size) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'color' => $variant['color'],
                    'size' => $size,
                    'original_price' => $variant['original_price'],
                    'sale_price' => $variant['sale_price'] ?? null,
                    'stock' => $variant['stock'],
                    'status' => 1,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Thêm sản phẩm thành công!');
    }


    // ===== FORM SỬA =====
    public function edit(Product $product)
    {
        $categories = Category::all();
        $variants   = $product->variants;
        return view('admin.products.edit', compact('product', 'categories', 'variants'));
    }

    // ===== CẬP NHẬT =====
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1,2',
            'thumbnail' => 'nullable|image|max:2048',
            'images.*' => 'nullable|image|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.color' => 'required|string|max:50',
            'variants.*.sizes' => 'required|array|min:1',
            'variants.*.original_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
        ]);

        // Cập nhật ảnh đại diện nếu có
        if ($request->hasFile('thumbnail')) {
            $product->thumbnail = $request->file('thumbnail')->store('products', 'public');
        }

        // Cập nhật ảnh bổ sung nếu có
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $img) {
                $imagePaths[] = $img->store('products', 'public');
            }
            $product->images = $imagePaths;
        }

        // Lấy slug từ name
        $slug = Str::slug($request->name);

        // Kiểm tra slug đã tồn tại ở sản phẩm khác chưa
        if (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
            return back()
                ->withErrors(['name' => 'Tên sản phẩm đã tồn tại.'])
                ->withInput();
        }

        // Lấy biến thể đầu tiên trong request
        $firstVariant = collect($request->variants)->first();

        // Cập nhật sản phẩm chính
        $product->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $firstVariant['original_price'] ?? 0,
            'status' => $request->status,
            'category_id' => $request->category_id,
        ]);

        $product->save(); // đảm bảo lưu thumbnail và images

        // Lấy danh sách ID biến thể từ form
        $variantIds = collect($request->variants)->pluck('id')->filter()->toArray();

        // Xóa biến thể không còn trong form
        if (!empty($variantIds)) {
            $product->variants()->whereNotIn('id', $variantIds)->delete();
        } else {
            $product->variants()->delete();
        }

        // Cập nhật hoặc tạo mới biến thể
        foreach ($request->variants as $variant) {
            foreach ($variant['sizes'] as $size) {
                ProductVariant::updateOrCreate(
                    [
                        'id' => $variant['id'] ?? null,
                    ],
                    [
                        'product_id' => $product->id,
                        'color' => $variant['color'],
                        'size' => $size,
                        'original_price' => $variant['original_price'],
                        'sale_price' => $variant['sale_price'] ?? null,
                        'stock' => $variant['stock'],
                        'status' => $variant['status'] ?? 1,
                    ]
                );
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm thành công!');
    }

    // ===== XEM CHI TIẾT =====
    public function show(Product $product)
    {
        $product->load('category', 'variants'); // nạp thêm quan hệ
        return view('admin.products.show', compact('product'));
    }


    // ===== XÓA =====
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Xóa sản phẩm thành công!');
    }
}
