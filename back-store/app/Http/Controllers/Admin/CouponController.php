<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::paginate(10);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'             => 'required|string|max:50|unique:coupons,code',
            'type'             => 'required|in:percent,fixed',

            // Giá trị tùy theo loại giảm giá
            'value' => [
                'required',
                'numeric',
                Rule::when($request->type === 'percent', function () {
                    return 'between:0,99';
                }),
                Rule::when($request->type === 'fixed', function () {
                    return 'min:1';
                }),
            ],

            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',

            // Validate ngày
            'starts_at'        => 'required|date|after_or_equal:today',
            'ends_at'          => 'required|date|after_or_equal:starts_at',

            'usage_limit'      => 'nullable|integer|min:1',

        ], [
            'code.required' => 'Vui lòng nhập mã voucher.',
            'code.unique'   => 'Mã voucher đã tồn tại.',

            'type.required' => 'Vui lòng chọn loại giảm giá.',

            'value.required'=> 'Vui lòng nhập giá trị giảm.',
            'value.numeric' => 'Giá trị giảm phải là số.',
            'value.between' => 'Giá trị phần trăm phải từ 0 đến 99%.',
            'value.min'     => 'Giá trị giảm cố định phải lớn hơn 0.',

            'min_order_amount.numeric' => 'Đơn hàng tối thiểu phải là số.',
            'max_discount.numeric'     => 'Giảm tối đa phải là số.',

            'starts_at.required' => 'Vui lòng nhập ngày bắt đầu.',
            'starts_at.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi.',

            'ends_at.required'   => 'Vui lòng nhập ngày kết thúc.',
            'ends_at.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',

            'usage_limit.integer' => 'Giới hạn số lần sử dụng phải là số nguyên.',
        ]);

        // checkbox active
        $data['active'] = $request->has('active') ? 1 : 0;

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Thêm voucher thành công!');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code'             => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'type'             => 'required|in:percent,fixed',

            // Rule theo điều kiện
            'value' => [
                'required',
                'numeric',
                Rule::when($request->type === 'percent', function () {
                    return 'between:0,99';
                }),
                Rule::when($request->type === 'fixed', function () {
                    return 'min:1';
                }),
            ],

            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',

            // Validate ngày
            'starts_at'        => 'required|date|after_or_equal:today',
            'ends_at'          => 'required|date|after_or_equal:starts_at',

            'usage_limit'      => 'nullable|integer|min:1',

        ], [
            'code.required' => 'Vui lòng nhập mã voucher.',
            'code.unique'   => 'Mã voucher đã tồn tại.',

            'type.required' => 'Vui lòng chọn loại giảm giá.',

            'value.required'=> 'Vui lòng nhập giá trị giảm.',
            'value.numeric' => 'Giá trị giảm phải là số.',
            'value.between' => 'Giá trị phần trăm phải từ 0 đến 99%.',
            'value.min'     => 'Giá trị giảm cố định phải lớn hơn 0.',

            'starts_at.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'ends_at.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',

            'usage_limit.integer' => 'Giới hạn số lần sử dụng phải là số nguyên.',
        ]);

        $data['active'] = $request->has('active') ? 1 : 0;

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Cập nhật voucher thành công!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Xóa voucher thành công!');
    }
}
