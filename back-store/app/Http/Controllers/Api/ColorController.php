<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    // Lấy danh sách màu
    public function index()
    {
        $colors = Color::all();
        return response()->json([
            'message' => 'Lấy danh sách màu thành công!',
            'data' => $colors
        ], 200);
    }

    // Thêm màu mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[\p{L}\p{N}\s\-\_]+$/u/', 
                'unique:colors,name'
            ],
            'code' => [
                'required',
                'string',
                'max:7',
                'regex:/^#([A-Fa-f0-9]{6})$/', 
                'unique:colors,code'
            ]
        ]);

        $color = Color::create($request->only('name', 'code'));

        return response()->json([
            'message' => 'Thêm màu thành công!',
            'data' => $color
        ], 201);
    }

    // Xóa màu
    public function destroy(Color $color)
    {
        if ($color->variants()->exists()) {
            return response()->json([
                'message' => 'Không thể xóa màu vì đang được sử dụng trong sản phẩm.'
            ], 400);
        }

        $color->delete();

        return response()->json([
            'message' => 'Xóa màu thành công!'
        ], 200);
    }
}
