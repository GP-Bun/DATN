<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminReviewController extends Controller
{
    /**
     * Lấy danh sách tất cả reviews (có phân trang và filter)
     */
    public function index(Request $request)
    {
        $query = Review::with(['user:id,name,email', 'product:id,name']);

        // Filter theo status
        if ($request->has('status')) {
            $status = $request->status;
            if ($status === 'active' || $status === '1') {
                $query->where('status', 1);
            } elseif ($status === 'inactive' || $status === '0') {
                $query->where('status', 0);
            }
        }

        // Filter theo product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter theo user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search theo comment
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('comment', 'like', "%{$search}%");
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reviews = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'reviews' => $reviews,
            'total' => $reviews->total(),
            'per_page' => $reviews->perPage(),
            'current_page' => $reviews->currentPage(),
            'last_page' => $reviews->lastPage(),
        ]);
    }

    /**
     * Lấy chi tiết một review
     */
    public function show($id)
    {
        $review = Review::with(['user:id,name,email', 'product:id,name,thumbnail'])
            ->findOrFail($id);

        return response()->json([
            'review' => $review
        ]);
    }

    /**
     * Cập nhật trạng thái review (duyệt/ẩn)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = Review::findOrFail($id);
        $review->status = $request->status;
        $review->save();

        // Ghi log hoạt động
        try {
            \App\Models\Activity::create([
                'user_id'    => $request->user()->id,
                'action'     => 'toggle_review_status',
                'description' => 'Admin đã ' . ($review->status ? 'duyệt' : 'ẩn') . ' review #' . $review->id,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Không thể ghi log Activity: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'review' => $review->load(['user:id,name,email', 'product:id,name'])
        ]);
    }

    /**
     * Xóa review
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        // Ghi log hoạt động
        try {
            \App\Models\Activity::create([
                'user_id'    => request()->user()->id,
                'action'     => 'delete_review',
                'description' => 'Admin đã xóa review #' . $review->id,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Không thể ghi log Activity: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Xóa review thành công'
        ]);
    }

    /**
     * Lấy thống kê reviews
     */
    public function stats()
    {
        $total = Review::count();
        $active = Review::where('status', 1)->count();
        $inactive = Review::where('status', 0)->count();
        $averageRating = Review::where('status', 1)->avg('rating');

        // Thống kê theo rating
        $ratingStats = Review::where('status', 1)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'average_rating' => round($averageRating, 2),
            'rating_stats' => $ratingStats,
        ]);
    }
}

