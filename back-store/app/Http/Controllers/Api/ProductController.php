<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductController extends Controller
{
    
    // GET LIST PRODUCTS
    
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants']);

        if ($request->has('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->paginate(10);

        return response()->json([
            'message' => 'Lấy danh sách sản phẩm thành công!',
            'data'    => $products
        ]);
    }

    
    // STORE PRODUCT
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'status'      => 'required|integer|in:0,1,2',
            'category_id' => 'required|exists:categories,id',

            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Variants
            'variants'                 => 'required|array',
            'variants.*.color_id'      => 'required|exists:colors,id',
            'variants.*.size_id'       => 'required|exists:sizes,id',
            'variants.*.original_price'=> 'required|numeric|min:0',
            'variants.*.sale_price'    => 'nullable|numeric|min:0',
            'variants.*.stock'         => 'required|integer|min:0',
            'variants.*.status'        => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        // Save THUMBNAIL
        $thumbnail = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail')
                ->store('products/thumbnails', 'public');
        }

        // MULTI IMAGES
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('products/images', 'public');
                $images[] = $path;
            }
        }

        // CREATE PRODUCT
        $product = Product::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->slug),
            'description' => $request->description,
            'price'       => $request->price,
            'status'      => $request->status,
            'category_id' => $request->category_id,
            'thumbnail'   => $thumbnail,
            'images'      => $images,
        ]);

        // CREATE VARIANTS
        foreach ($request->variants as $v) {
            ProductVariant::create([
                'product_id'     => $product->id,
                'color_id'       => $v['color_id'],
                'size_id'        => $v['size_id'],
                'original_price' => $v['original_price'],
                'sale_price'     => $v['sale_price'] ?? null,
                'stock'          => $v['stock'],
                'status'         => $v['status'],
            ]);
        }

        return response()->json([
            'message' => 'Thêm sản phẩm thành công!',
            'data'    => $product->load('variants')
        ]);
    }

    
    // SHOW PRODUCT
    
    public function show($id)
    {
        $product = Product::with(['category', 'variants'])->find($id);

        if (!$product) {
            return response()->json(['message'=>'Không tìm thấy sản phẩm!'], 404);
        }

        return response()->json([
            'message' => 'Lấy sản phẩm thành công!',
            'data'    => $product
        ]);
    }

    
    // UPDATE PRODUCT
    
    public function update(Request $request, $id)
    {
        $product = Product::with('variants')->find($id);
        if (!$product) {
            return response()->json(['message'=>'Không tìm thấy sản phẩm!'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'slug'        => "required|string|max:255|unique:products,slug,$id",
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'status'      => 'required|integer|in:0,1,2',
            'category_id' => 'required|exists:categories,id',
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'variants'                  => 'required|array',
            'variants.*.id'             => 'nullable|exists:product_variants,id',
            'variants.*.color_id'       => 'required|exists:colors,id',
            'variants.*.size_id'        => 'required|exists:sizes,id',
            'variants.*.original_price' => 'required|numeric|min:0',
            'variants.*.sale_price'     => 'nullable|numeric|min:0',
            'variants.*.stock'          => 'required|integer|min:0',
            'variants.*.status'         => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        // UPDATE THUMBNAIL
        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $product->thumbnail = $request->file('thumbnail')
                ->store('products/thumbnails', 'public');
        }

        // ADD MORE IMAGES (không xoá ảnh cũ)
        $images = $product->images ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $images[] = $img->store('products/images', 'public');
            }
        }
        $product->images = $images;

        // UPDATE MAIN INFO
        $product->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->slug),
            'description' => $request->description,
            'price'       => $request->price,
            'status'      => $request->status,
            'category_id' => $request->category_id,
        ]);

        // UPDATE VARIANTS
        $existingIDs = $product->variants->pluck('id')->toArray();
        $newIDs = [];

        foreach ($request->variants as $v) {
            if (isset($v['id'])) {
                $variant = ProductVariant::find($v['id']);
                $variant->update([
                    'color_id'       => $v['color_id'],
                    'size_id'        => $v['size_id'],
                    'original_price' => $v['original_price'],
                    'sale_price'     => $v['sale_price'] ?? null,
                    'stock'          => $v['stock'],
                    'status'         => $v['status'],
                ]);
                $newIDs[] = $variant->id;
            } else {
                $new = ProductVariant::create([
                    'product_id'     => $product->id,
                    'color_id'       => $v['color_id'],
                    'size_id'        => $v['size_id'],
                    'original_price' => $v['original_price'],
                    'sale_price'     => $v['sale_price'] ?? null,
                    'stock'          => $v['stock'],
                    'status'         => $v['status'],
                ]);
                $newIDs[] = $new->id;
            }
        }

        // XÓA VARIANT KHÔNG CÒN TRONG REQUEST
        $toDelete = array_diff($existingIDs, $newIDs);
        ProductVariant::whereIn('id', $toDelete)->delete();

        return response()->json([
            'message' => 'Cập nhật sản phẩm thành công!',
            'data'    => $product->fresh()->load('variants')
        ]);
    }

    
    // SOFT DELETE
    
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message'=>'Không tìm thấy sản phẩm!'], 404);
        }

        $product->delete();

        return response()->json(['message'=>'Đã chuyển sản phẩm vào thùng rác!']);
    }

    
    // LIST TRASHED
    
    public function trash()
    {
        $products = Product::onlyTrashed()->paginate(10);

        return response()->json([
            'message'=>'Danh sách sản phẩm trong thùng rác!',
            'data'=>$products
        ]);
    }

    
    // RESTORE
    
    public function restore($id)
    {
        $product = Product::onlyTrashed()->find($id);

        if (!$product) {
            return response()->json(['message'=>'Không tìm thấy sản phẩm trong thùng rác!'], 404);
        }

        $product->restore();

        return response()->json(['message'=>'Khôi phục sản phẩm thành công!']);
    }

    
    // FORCE DELETE
    
    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->with('variants')->find($id);
        if (!$product) {
            return response()->json(['message'=>'Không tìm thấy sản phẩm!'], 404);
        }

        // XÓA FILE ẢNH
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }
        if ($product->images) {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        // XÓA VARIANTS
        foreach ($product->variants as $v) {
            $v->forceDelete();
        }

        $product->forceDelete();

        return response()->json(['message'=>'Xóa vĩnh viễn sản phẩm thành công!']);
    }
}
