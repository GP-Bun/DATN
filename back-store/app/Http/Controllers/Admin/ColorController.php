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
            'code' => 'required|string|max:7',
        ]);

        Color::create($request->only('name','code'));

        return redirect()->back()->with('success','Thêm màu thành công!');
    }
}
