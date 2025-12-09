<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;

class CartController extends Controller
{
    // Lấy giỏ hàng
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $items = $cart->items()->with(['product','variant'])->get();
        $total = $items->sum(fn($i) => $i->price * $i->quantity);

        return response()->json([
            'items' => $items,
            'total' => $total
        ]);
    }

    // Thêm sản phẩm
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $product = Product::findOrFail($request->product_id);

        $price = $request->variant_id 
            ? ProductVariant::find($request->variant_id)->price 
            : $product->price;

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $request->variant_id)
            ->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            $item = CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'variant_id' => $request->variant_id,
                'quantity'   => $request->quantity,
                'price'      => $price,
            ]);
        }

        return response()->json(['message' => 'Đã thêm vào giỏ hàng', 'item' => $item]);
    }

    // Cập nhật số lượng
    public function update(Request $request, CartItem $item)
    {
        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền'], 403);
        }

        $request->validate(['quantity' => 'required|integer|min:1']);
        $item->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cập nhật thành công', 'item' => $item]);
    }

    // Xóa sản phẩm
    public function remove(Request $request, CartItem $item)
    {
        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền'], 403);
        }

        $item->delete();
        return response()->json(['message' => 'Đã xóa sản phẩm']);
    }

    // Xóa toàn bộ giỏ
    public function clear(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if ($cart) {
            $cart->items()->delete();
        }
        return response()->json(['message' => 'Đã xóa toàn bộ giỏ hàng']);
    }
}
