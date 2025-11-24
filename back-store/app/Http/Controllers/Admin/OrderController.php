<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(){
        $orders = Order::with('user')->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order){
        $order->load('items.product','items.variant','user');
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order){
        $request->validate(['status'=>'required|string']);
        $order->update(['status'=>$request->status]);
        return redirect()->back()->with('success','Cập nhật trạng thái thành công!');
    }

    public function destroy(Order $order){
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success','Xóa đơn hàng thành công!');
    }
}
