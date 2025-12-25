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
        $items = $cart->items()->with(['product', 'variant.color', 'variant.size'])->get();

        // Chuyển đổi đường dẫn ảnh thành URL đầy đủ
        $items->transform(function ($item) {
            if ($item->product) {
                if ($item->product->thumbnail) {
                    $item->product->image = url('storage/' . $item->product->thumbnail);
                    $item->product->thumbnail_url = url('storage/' . $item->product->thumbnail);
                } elseif ($item->product->images && is_array($item->product->images) && count($item->product->images) > 0) {
                    $item->product->image = url('storage/' . $item->product->images[0]);
                }
            }
            return $item;
        });

        $total = $items->sum(fn($i) => $i->price * $i->quantity);

        return response()->json([
            'data' => $items,
            'items' => $items,
            'total' => $total
        ]);
    }

    // Thêm sản phẩm vào giỏ
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $product = Product::findOrFail($request->product_id);

        // Kiểm tra trạng thái sản phẩm
        if ($product->status == 0) {
            return response()->json(['message' => 'Sản phẩm đã bị ẩn'], 400);
        }
        if ($product->status == 2) {
            return response()->json(['message' => 'Sản phẩm đã hết hàng'], 400);
        }

        // Kiểm tra tồn kho và lấy giá
        if ($request->variant_id) {
            $variant = ProductVariant::findOrFail($request->variant_id);

            if ($variant->status == 2 || $variant->stock < $request->quantity) {
                return response()->json(['message' => 'Biến thể này đã hết hàng hoặc không đủ số lượng'], 400);
            }

            $price = $variant->sale_price ?? $variant->original_price;
        } else {
            if ($product->stock < $request->quantity) {
                return response()->json(['message' => 'Sản phẩm không đủ số lượng'], 400);
            }

            $price = $product->price;
        }

        // Thêm hoặc cập nhật item trong giỏ
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $request->variant_id)
            ->first();

        if ($item) {
            $newQuantity = $item->quantity + $request->quantity;

            // Kiểm tra lại tồn kho khi cộng thêm số lượng
            if ($request->variant_id) {
                $variant = ProductVariant::findOrFail($request->variant_id);
                if ($variant->stock < $newQuantity) {
                    return response()->json(['message' => 'Số lượng vượt quá tồn kho của biến thể'], 400);
                }
            } else {
                if ($product->stock < $newQuantity) {
                    return response()->json(['message' => 'Số lượng vượt quá tồn kho của sản phẩm'], 400);
                }
            }

            $item->quantity = $newQuantity;
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

        // Kiểm tra tồn kho trước khi cập nhật
        if ($item->variant_id) {
            $variant = ProductVariant::findOrFail($item->variant_id);
            if ($variant->stock < $request->quantity) {
                return response()->json(['message' => 'Số lượng vượt quá tồn kho của biến thể'], 400);
            }
        } else {
            $product = Product::findOrFail($item->product_id);
            if ($product->stock < $request->quantity) {
                return response()->json(['message' => 'Số lượng vượt quá tồn kho của sản phẩm'], 400);
            }
        }

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
