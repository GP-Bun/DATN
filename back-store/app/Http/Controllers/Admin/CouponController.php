<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(){
        $coupons = Coupon::paginate(10);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(){
        return view('admin.coupons.create');
    }

    public function store(Request $request){
        $request->validate([
            'code'=>'required|string|unique:coupons,code',
            'type'=>'required|in:percent,fixed',
            'value'=>'required|numeric|min:0',
            'expires_at'=>'required|date',
            'status'=>'required|in:ACTIVE,INACTIVE'
        ]);

        Coupon::create($request->all());
        return redirect()->route('admin.coupons.index')->with('success','Thêm mã giảm giá thành công!');
    }

    public function destroy(Coupon $coupon){
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success','Xóa mã giảm giá thành công!');
    }
}
