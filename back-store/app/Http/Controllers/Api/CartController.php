<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Product;

class CartController extends Controller
{
    // Lấy giỏ hàng
    public function index()
    {
        $cart = Session::get('cart', []);
        return response()->json([
            'message' => 'Lấy giỏ hàng thành công!',
            'data' => array_values($cart)
        ]);
    }

    // Thêm sản phẩm
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'color' => 'nullable|string',
            'size' => 'nullable|string',
        ]);

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại!'], 404);
        }

        $cart = Session::get('cart', []);

        $id = $product->id . '-' . ($request->color ?? 'default') . '-' . ($request->size ?? 'default');

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $request->quantity;
        } else {
            $cart[$id] = [
                'id' => $id,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->thumbnail,
                'quantity' => $request->quantity,
                'color' => $request->color,
                'size' => $request->size,
            ];
        }

        Session::put('cart', $cart);

        return response()->json([
            'message' => 'Đã thêm vào giỏ hàng!',
            'data' => array_values($cart)
        ]);
    }

    // Cập nhật số lượng
    public function update(Request $request, $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Session::get('cart', []);

        if (!isset($cart[$item])) {
            return response()->json(['message' => 'Item không tồn tại!'], 404);
        }

        $cart[$item]['quantity'] = $request->quantity;
        Session::put('cart', $cart);

        return response()->json([
            'message' => 'Cập nhật thành công!',
            'data' => array_values($cart)
        ]);
    }

    // Xóa item
    public function remove($item)
    {
        $cart = Session::get('cart', []);
        unset($cart[$item]);
        Session::put('cart', $cart);

        return response()->json([
            'message' => 'Xóa thành công!',
            'data' => array_values($cart)
        ]);
    }

    // Xóa toàn bộ
    public function clear()
    {
        Session::forget('cart');

        return response()->json([
            'message' => 'Đã xóa toàn bộ giỏ hàng!',
            'data' => []
        ]);
    }
}
