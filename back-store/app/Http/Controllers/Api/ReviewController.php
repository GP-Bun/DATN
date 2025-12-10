<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Lấy danh sách review của sản phẩm
     */
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->where('status', 1)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        $averageRating = Review::where('product_id', $productId)
            ->where('status', 1)
            ->avg('rating');

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
     * Thêm review mới
     */
    public function store(Request $request, $productId)
    {

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Bạn cần đăng nhập để bình luận'
            ], 403);
        }

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

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $user = $request->user();

        $review = Review::create([
            'product_id' => $productId,
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : $request->user_name,
            'user_email' => $user ? $user->email : $request->user_email,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 1,
        ]);

        $review->load('user:id,name');

        // ✅ chỉ ghi log nếu có user đăng nhập
        Activity::create([
            'user_id'    => $user ? $user->id : null,
            'action'     => 'review',
            'description' => $user
                ? 'Người dùng ' . $user->name . ' đã đánh giá sản phẩm ' . $product->name
                : 'Khách hàng ' . $request->user_name . ' (' . $request->user_email . ') đã đánh giá sản phẩm ' . $product->name,
        ]);


        return response()->json([
            'message' => 'Đánh giá đã được thêm thành công',
            'review' => $review
        ], 201);
    }

    /**
     * Cập nhật review
     */
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $user = $request->user();

        $this->authorize('update', $review);

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        Activity::create([
            'user_id'    => $user->id,
            'action'     => 'update_review',
            'description' => 'Người dùng đã chỉnh sửa review #' . $review->id,
        ]);

        return response()->json([
            'message' => 'Đánh giá đã được cập nhật thành công',
            'review' => $review
        ]);
    }

    /**
     * Xoá review
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $user = request()->user();

        $this->authorize('delete', $review);

        $review->delete();

        Activity::create([
            'user_id'    => $user->id,
            'action'     => 'delete_review',
            'description' => 'Người dùng đã xoá review #' . $review->id,
        ]);

        return response()->json(['message' => 'Đánh giá đã được xóa']);
    }

    /**
     * Admin duyệt/ẩn review
     */
    public function toggleStatus($id)
    {
        $review = Review::findOrFail($id);
        $user = request()->user();

        if ($user && $user->role === 'admin') {
            $review->status = $review->status ? 0 : 1;
            $review->save();

            Activity::create([
                'user_id'    => $user->id,
                'action'     => 'toggle_review_status',
                'description' => 'Admin đã ' . ($review->status ? 'duyệt' : 'ẩn') . ' review #' . $review->id,
            ]);

            return response()->json([
                'message' => 'Trạng thái review đã được cập nhật',
                'review' => $review
            ]);
        }

        return response()->json(['message' => 'Bạn không có quyền duyệt/ẩn review này'], 403);
    }
}
