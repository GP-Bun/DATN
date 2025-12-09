<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:colors,name',
            'code' => 'required|string|max:7|regex:/^#([A-Fa-f0-9]{6})$/|unique:colors,code',
        ], [
            'name.required' => 'Bạn phải nhập tên màu.',
            'name.string'   => 'Tên màu phải là chuỗi ký tự.',
            'name.max'      => 'Tên màu không được vượt quá 50 ký tự.',
            'name.unique'   => 'Tên màu này đã tồn tại.',

            'code.required' => 'Bạn phải nhập mã màu.',
            'code.string'   => 'Mã màu phải là chuỗi ký tự.',
            'code.max'      => 'Mã màu không được vượt quá 7 ký tự.',
            'code.regex'    => 'Mã màu phải đúng định dạng HEX, ví dụ: #FFFFFF.',
            'code.unique'   => 'Mã màu này đã tồn tại.',
        ]);

        Color::create($request->only('name', 'code'));

        return redirect()->back()->with('success', 'Thêm màu thành công!');
    }

    public function destroy(Color $color)
    {
        if ($color->variants()->exists()) {
            return back()->with('error', 'Không thể xóa màu vì đang được sử dụng trong sản phẩm.');
        }

        $color->delete();

        return back()->with('success', 'Xóa màu thành công');
    }
}
