<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    // GET /api/categories
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Category::orderBy('id', 'desc')->get()
        ]);
    }

    // GET /api/categories/{id}
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }

        return response()->json(['status' => true, 'data' => $category]);
    }

    // POST /api/categories
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[\p{L}\p{N}\s\-\_]+$/u',
                    'unique:categories,name'
                ],
                'description' => 'nullable|string|max:2000',
            ], [
                'name.required' => 'Tên danh mục không được để trống.',
                'name.max' => 'Tên danh mục tối đa 255 ký tự.',
                'name.regex' => 'Tên chỉ được chứa chữ, số, khoảng trắng và dấu - _',
                'name.unique' => 'Tên danh mục đã tồn tại.',
                'description.max' => 'Mô tả tối đa 2000 ký tự.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        }

        // Tạo slug unique
        $slug = Str::slug($request->name);
        $orig = $slug; $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $orig . '-' . $i++;
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thêm danh mục thành công!',
            'data' => $category
        ], 201);
    }

    // PUT /api/categories/{id}
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[\p{L}\p{N}\s\-\_]+$/u',
                    'unique:categories,name,' . $id
                ],
                'description' => 'nullable|string|max:2000',
            ], [
                'name.required' => 'Tên danh mục không được để trống.',
                'name.max' => 'Tên danh mục tối đa 255 ký tự.',
                'name.regex' => 'Tên chỉ được chứa chữ, số, khoảng trắng và dấu - _',
                'name.unique' => 'Tên danh mục đã tồn tại.',
                'description.max' => 'Mô tả tối đa 2000 ký tự.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        }

        // Generate unique slug
        $slug = Str::slug($request->name);
        $orig = $slug; $i = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $orig . '-' . $i++;
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật danh mục thành công!',
            'data' => $category
        ]);
    }

    // DELETE /api/categories/{id}
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa danh mục thành công!'
        ]);
    }
}
