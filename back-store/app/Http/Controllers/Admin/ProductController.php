<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;

class ProductController extends Controller
{
    // ===== DANH SÁCH =====
    public function index()
    {
        $products = Product::with(['category', 'variants.color', 'variants.size'])->latest()->paginate(10);
        $trashCount = Product::onlyTrashed()->count();

        return view('admin.products.index', compact('products', 'trashCount'));
    }


    // ===== FORM THÊM =====
    public function create()
    {
        $categories = Category::all();
        $colors = Color::all();
        $sizes  = Size::all();
        return view('admin.products.create', compact('categories', 'colors', 'sizes'));
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
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.sizes' => 'required|array|min:1',
            'variants.*.sizes.*' => 'exists:sizes,id',
            'variants.*.original_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
        ], [
            // Sản phẩm
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'thumbnail.image' => 'Ảnh đại diện phải là hình ảnh.',
            'thumbnail.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            'images.*.image' => 'Ảnh bổ sung phải là hình ảnh.',
            'images.*.max' => 'Ảnh bổ sung không được vượt quá 2MB.',
            // Biến thể
            'variants.required' => 'Phải có ít nhất một biến thể.',
            'variants.array' => 'Dữ liệu biến thể không hợp lệ.',
            'variants.*.color_id.required' => 'Vui lòng chọn màu cho biến thể.',
            'variants.*.color_id.exists' => 'Màu đã chọn không hợp lệ.',
            'variants.*.sizes.required' => 'Vui lòng chọn ít nhất một size.',
            'variants.*.sizes.array' => 'Danh sách size không hợp lệ.',
            'variants.*.sizes.*.exists' => 'Size đã chọn không hợp lệ.',
            'variants.*.original_price.required' => 'Giá gốc là bắt buộc.',
            'variants.*.original_price.numeric' => 'Giá gốc phải là số.',
            'variants.*.original_price.min' => 'Giá gốc không được nhỏ hơn 0.',
            'variants.*.sale_price.numeric' => 'Giá giảm phải là số.',
            'variants.*.sale_price.min' => 'Giá giảm không được nhỏ hơn 0.',
            'variants.*.stock.required' => 'Số lượng tồn kho là bắt buộc.',
            'variants.*.stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'variants.*.stock.min' => 'Số lượng tồn kho không được nhỏ hơn 0.',
        ]);

        // Kiểm tra trùng lặp color + size trong request
        $combos = [];
        foreach ($request->variants as $variant) {
            foreach ($variant['sizes'] as $sizeId) {
                $key = $variant['color_id'] . '-' . $sizeId;
                if (in_array($key, $combos)) {
                    return back()
                        ->withErrors(['variants' => 'Size này đã có màu này rồi.'])
                        ->withInput();
                }
                $combos[] = $key;
            }
        }

        $slug = Str::slug($request->name);

        if (Product::withTrashed()->where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'Tên sản phẩm đã tồn tại.'])->withInput();
        }

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
            'is_featured' => $request->has('is_featured') ? 1 : 0,
            'category_id' => $request->category_id,
            'thumbnail' => $thumbnailPath,
            'images' => $imagePaths,
        ]);

        foreach ($request->variants as $variant) {
            foreach ($variant['sizes'] as $sizeId) {
                ProductVariant::create([
                    'product_id'     => $product->id,
                    'color_id'       => $variant['color_id'],
                    'size_id'        => $sizeId,
                    'original_price' => $variant['original_price'],
                    'sale_price'     => $variant['sale_price'] ?? null,
                    'stock'          => $variant['stock'],
                    'status'         => 1,
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
        $colors = Color::all();
        $sizes  = Size::all();
        return view('admin.products.edit', compact('product', 'categories', 'variants', 'colors', 'sizes'));
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
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.sizes' => 'required|array|min:1',
            'variants.*.sizes.*' => 'exists:sizes,id',
            'variants.*.original_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
        ], [
            // Sản phẩm
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'thumbnail.image' => 'Ảnh đại diện phải là hình ảnh.',
            'thumbnail.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            'images.*.image' => 'Ảnh bổ sung phải là hình ảnh.',
            'images.*.max' => 'Ảnh bổ sung không được vượt quá 2MB.',

            // Biến thể
            'variants.required' => 'Phải có ít nhất một biến thể.',
            'variants.array' => 'Dữ liệu biến thể không hợp lệ.',
            'variants.*.color_id.required' => 'Vui lòng chọn màu cho biến thể.',
            'variants.*.color_id.exists' => 'Màu đã chọn không hợp lệ.',
            'variants.*.sizes.required' => 'Vui lòng chọn ít nhất một size.',
            'variants.*.sizes.array' => 'Danh sách size không hợp lệ.',
            'variants.*.sizes.*.exists' => 'Size đã chọn không hợp lệ.',
            'variants.*.original_price.required' => 'Giá gốc là bắt buộc.',
            'variants.*.original_price.numeric' => 'Giá gốc phải là số.',
            'variants.*.original_price.min' => 'Giá gốc không được nhỏ hơn 0.',
            'variants.*.sale_price.numeric' => 'Giá giảm phải là số.',
            'variants.*.sale_price.min' => 'Giá giảm không được nhỏ hơn 0.',
            'variants.*.stock.required' => 'Số lượng tồn kho là bắt buộc.',
            'variants.*.stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'variants.*.stock.min' => 'Số lượng tồn kho không được nhỏ hơn 0.',
        ]);

        // Kiểm tra trùng lặp color + size trong request
        $combos = [];
        foreach ($request->variants as $variant) {
            foreach ($variant['sizes'] as $sizeId) {
                $key = $variant['color_id'] . '-' . $sizeId;
                if (in_array($key, $combos)) {
                    return back()
                        ->withErrors(['variants' => 'Size này đã có màu này rồi.'])
                        ->withInput();
                }
                $combos[] = $key;
            }
        }

        if ($request->hasFile('thumbnail')) {
            $product->thumbnail = $request->file('thumbnail')->store('products', 'public');
        }

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $img) {
                $imagePaths[] = $img->store('products', 'public');
            }
            $product->images = $imagePaths;
        }

        $slug = Str::slug($request->name);

        if (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
            return back()->withErrors(['name' => 'Tên sản phẩm đã tồn tại.'])->withInput();
        }

        $firstVariant = collect($request->variants)->first();

        $product->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $firstVariant['original_price'] ?? 0,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured') ? 1 : 0,
            'category_id' => $request->category_id,
        ]);

        $product->save();

        $variantIds = collect($request->variants)->pluck('id')->filter()->toArray();

        if (!empty($variantIds)) {
            $product->variants()->whereNotIn('id', $variantIds)->delete();
        } else {
            $product->variants()->delete();
        }

        foreach ($request->variants as $variant) {
            foreach ($variant['sizes'] as $sizeId) {
                $match = [
                    'product_id' => $product->id,
                    'color_id'   => $variant['color_id'],
                    'size_id'    => $sizeId,
                ];

                // Nếu có id thì ưu tiên match theo id để tránh ghi đè nhầm
                if (!empty($variant['id'])) {
                    $match['id'] = $variant['id'];
                }

                ProductVariant::updateOrCreate($match, [
                    'product_id'     => $product->id,
                    'color_id'       => $variant['color_id'],
                    'size_id'        => $sizeId,
                    'original_price' => $variant['original_price'],
                    'sale_price'     => $variant['sale_price'] ?? null,
                    'stock'          => $variant['stock'],
                    'status'         => $variant['status'] ?? 1,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm thành công!');
    }

    // ===== XEM CHI TIẾT =====
    public function show(Product $product)
    {
        $product->load('category', 'variants.color', 'variants.size');
        return view('admin.products.show', compact('product'));
    }

    // ===== XÓA =====
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Xóa sản phẩm thành công!');
    }

    public function trash()
    {
        $products = Product::onlyTrashed()->with('category')->latest('deleted_at')->paginate(10);
        return view('admin.products.trash', compact('products'));
    }

    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return redirect()->route('admin.products.trash')
            ->with('success', 'Khôi phục sản phẩm thành công!');
    }

    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->forceDelete();

        return redirect()->route('admin.products.trash')
            ->with('success', 'Xóa vĩnh viễn sản phẩm thành công!');
    }
}
