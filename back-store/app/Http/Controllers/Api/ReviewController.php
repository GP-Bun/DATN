<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Lấy danh sách đánh giá của sản phẩm
     */
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->where('status', 1)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        // Tính điểm trung bình
        $averageRating = Review::where('product_id', $productId)
            ->where('status', 1)
            ->avg('rating');

        // Đếm số lượng đánh giá theo từng sao
        $ratingCounts = Review::where('product_id', $productId)
            ->where('status', 1)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $reviews->count(),
            'rating_counts' => $ratingCounts,
        ]);
    }

    /**
     * Thêm đánh giá mới
     */
    public function store(Request $request, $productId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'user_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Kiểm tra sản phẩm có tồn tại không
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        // Nếu user đã đăng nhập, lấy thông tin từ auth
        $user = $request->user();
        
        $review = Review::create([
            'product_id' => $productId,
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : $request->user_name,
            'user_email' => $user ? $user->email : $request->user_email,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 1, // Tự động hiển thị (có thể thay đổi thành 0 nếu cần admin duyệt)
        ]);

        $review->load('user:id,name');

        return response()->json([
            'message' => 'Đánh giá đã được thêm thành công',
            'review' => $review
        ], 201);
    }

    /**
     * Xóa đánh giá (chỉ user đã tạo hoặc admin)
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $user = request()->user();

        // Chỉ cho phép xóa nếu là chủ sở hữu hoặc admin
        if ($user && ($review->user_id === $user->id || $user->role === 'admin')) {
            $review->delete();
            return response()->json([
                'message' => 'Đánh giá đã được xóa'
            ]);
        }

        return response()->json([
            'message' => 'Bạn không có quyền xóa đánh giá này'
        ], 403);
    }
}

