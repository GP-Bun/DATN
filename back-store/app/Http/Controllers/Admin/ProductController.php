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
    // ===== Tạo slug duy nhất =====
    protected function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (
            Product::withTrashed()
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    // ===== DANH SÁCH SẢN PHẨM =====
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
      public function store(Request $request){
        // dd($request->all());
        $request->validate([
            'name'=>'required|string|max:255',
            'category_id'=>'required|exists:categories,id',
            'price'=>'required|numeric|min:0',
            'description'=>'nullable|string',
            'thumbnail'=>'nullable|image|max:2048',  
            'images.*'=>'nullable|image|max:2048',
            'variants'=>'required|array',
            'variants.*.color'=>'required|string|max:50',
            'variants.*.sizes'=>'required|array|min:1',
            'variants.*.original_price'=>'required|numeric|min:0',
            'variants.*.sale_price'=>'nullable|numeric|min:0',
            'variants.*.stock'=>'required|integer|min:0'
        ]);

        $thumbnail = $request->hasFile('thumbnail') ? $request->file('thumbnail')->store('products','public') : null;

        $images = [];
        if($request->hasFile('images')){
            foreach($request->file('images') as $img){
                $images[] = $img->store('products','public');
            }
        }

        $product = Product::create([
            'name'=>$request->name,
            'slug'=>$this->generateUniqueSlug($request->name),
            'description'=>$request->description,
            'price'=>$request->price,
            'status'=>1,
            'category_id'=>$request->category_id,
            'thumbnail'=>$thumbnail,
            'images'=>$images
        ]);

        foreach($request->variants as $v){
            foreach($v['sizes'] as $size){
                ProductVariant::create([
                    'product_id'=>$product->id,
                    'color'=>$v['color'],
                    'size'=>$size,
                    'original_price'=>$v['original_price'],
                    'sale_price'=>$v['sale_price'] ?? null,
                    'stock'=>$v['stock']
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success','Thêm sản phẩm thành công');
    }


    // ===== FORM SỬA =====
    public function edit(Product $product)
    {
        $categories = Category::all();
        $variants   = $product->variants;
        return view('admin.products.edit', compact('product', 'categories', 'variants'));
    }

    // ===== CẬP NHẬT SẢN PHẨM =====
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'status'      => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'thumbnail'   => 'nullable|image|max:2048',
            'images.*'    => 'nullable|image|max:2048',
            'variants'    => 'required|array',
            'variants.*.color'          => 'required|string|max:50',
            'variants.*.sizes'          => 'required|array|min:1',
            'variants.*.original_price' => 'required|numeric|min:0',
            'variants.*.sale_price'     => 'nullable|numeric|min:0',
            'variants.*.stock'          => 'required|integer|min:0',
        ]);

        // Slug mới
        $slug = $this->generateUniqueSlug($request->name, $product->id);

        // Thumbnail mới
        if ($request->hasFile('thumbnail')) {
            $product->thumbnail = $request->file('thumbnail')->store('products', 'public');
        }

        // Thêm ảnh mới
        $imagePaths = $product->images ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $imagePaths[] = $img->store('products', 'public');
            }
        }

        // Cập nhật sản phẩm
        $product->update([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'price'       => $request->price,
            'status'      => $request->status,
            'category_id' => $request->category_id,
            'images'      => $imagePaths,
        ]);

        // Xóa biến thể cũ và tạo lại
        $product->variants()->delete();
        foreach ($request->variants as $variant) {
            foreach ($variant['sizes'] as $size) {
                ProductVariant::create([
                    'product_id'     => $product->id,
                    'color'          => $variant['color'],
                    'size'           => $size,
                    'original_price' => $variant['original_price'],
                    'sale_price'     => $variant['sale_price'] ?? null,
                    'stock'          => $variant['stock'],
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    // ===== XÓA SẢN PHẨM (SOFT DELETE) =====
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Xóa sản phẩm thành công!');
    }
}
