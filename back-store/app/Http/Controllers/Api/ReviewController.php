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
        // Yêu cầu user phải đăng nhập
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Bạn cần đăng nhập để bình luận'
            ], 403);
        }

        // Chỉ validate rating và comment, không cần user_name và user_email
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

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        // Lấy thông tin từ user đăng nhập
        $review = Review::create([
            'product_id' => $productId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 1,
        ]);

        $review->load('user:id,name');

        // Ghi log hoạt động (không làm fail nếu có lỗi)
        try {
            Activity::create([
                'user_id'    => $user->id,
                'action'     => 'review',
                'description' => 'Người dùng ' . $user->name . ' đã đánh giá sản phẩm ' . $product->name,
            ]);
        } catch (\Exception $e) {
            // Bỏ qua lỗi Activity, không ảnh hưởng đến việc tạo review
            \Log::warning('Không thể ghi log Activity: ' . $e->getMessage());
        }

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

        // Ghi log hoạt động (không làm fail nếu có lỗi)
        try {
            Activity::create([
                'user_id'    => $user->id,
                'action'     => 'update_review',
                'description' => 'Người dùng đã chỉnh sửa review #' . $review->id,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Không thể ghi log Activity: ' . $e->getMessage());
        }

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

        // Ghi log hoạt động (không làm fail nếu có lỗi)
        try {
            Activity::create([
                'user_id'    => $user->id,
                'action'     => 'delete_review',
                'description' => 'Người dùng đã xoá review #' . $review->id,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Không thể ghi log Activity: ' . $e->getMessage());
        }

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

            // Ghi log hoạt động (không làm fail nếu có lỗi)
            try {
                Activity::create([
                    'user_id'    => $user->id,
                    'action'     => 'toggle_review_status',
                    'description' => 'Admin đã ' . ($review->status ? 'duyệt' : 'ẩn') . ' review #' . $review->id,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Không thể ghi log Activity: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Trạng thái review đã được cập nhật',
                'review' => $review
            ]);
        }

        return response()->json(['message' => 'Bạn không có quyền duyệt/ẩn review này'], 403);
    }
}
