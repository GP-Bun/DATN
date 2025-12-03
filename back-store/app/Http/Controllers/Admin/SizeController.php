<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Size;

class SizeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required|integer|unique:sizes,value',
        ]);

        Size::create($request->only('value'));

        return redirect()->back()->with('success','Thêm size thành công!');
    }
}
