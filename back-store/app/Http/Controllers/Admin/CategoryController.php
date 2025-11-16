<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('admin.categories.index', compact('categories'));
    }
    public function create()
{
    return view('admin.categories.create');
}

public function store(Request $request)
{
    
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ]);

    $slug = Str::slug($request->name);
    $originalSlug = $slug;
    $counter = 1;

    // Nếu slug đã tồn tại, thêm hậu tố -1, -2, ...
    while (Category::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter++;
    }

    // Tạo danh mục
    Category::create([
        'name' => $request->name,
        'slug' => $slug,
        'description' => $request->description,
    ]);

   
    return redirect()->route('admin.categories.index')->with('success', 'Thêm danh mục thành công!');
}

public function edit(Category $category)
{
    return view('admin.categories.edit', compact('category'));
}

public function update(Request $request, Category $category)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ]);

    $slug = Str::slug($request->name);
    $originalSlug = $slug;
    $counter = 1;

    // Kiểm tra slug trùng, trừ chính nó ra
    while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
        $slug = $originalSlug . '-' . $counter++;
    }

    $category->update([
        'name' => $request->name,
        'slug' => $slug,
        'description' => $request->description,
    ]);

    return redirect()->route('admin.categories.index')->with('success', 'Cập nhật danh mục thành công!');
}
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Xóa danh mục thành công!');
    }

}
