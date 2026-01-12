<?php 
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Size;

class SizeController extends Controller
{
    // Lấy danh sách size
    public function index()
    {
        $sizes = Size::all();
        return response()->json([
            'message' => 'Lấy danh sách size thành công!',
            'data' => $sizes
        ], 200);
    }

    // Thêm size mới
    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required|integer|unique:sizes,value',
        ]);

        $size = Size::create($request->only('value'));

        return response()->json([
            'message' => 'Thêm size thành công!',
            'data' => $size
        ], 201);
    }

    // Xóa size
    public function destroy(Size $size)
    {
        // Nếu size đang được dùng trong product_variants thì không cho xoá
        if ($size->variants()->exists()) {
            return response()->json([
                'message' => 'Không thể xóa size vì đang được sử dụng trong sản phẩm.'
            ], 400);
        }

        $size->delete();

        return response()->json([
            'message' => 'Xóa size thành công!'
        ], 200);
    }
}
