<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Danh sách tất cả reviews
     */
    public function index(Request $request)
    {
        $query = Review::with(['user:id,name,email', 'product:id,name,thumbnail']);

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

        // Search theo comment
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('comment', 'like', "%{$search}%");
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reviews = $query->paginate(15);

        // Lấy danh sách sản phẩm cho filter
        $products = Product::select('id', 'name')->orderBy('name')->get();

        // Thống kê
        $stats = [
            'total' => Review::count(),
            'active' => Review::where('status', 1)->count(),
            'inactive' => Review::where('status', 0)->count(),
            'average_rating' => round(Review::where('status', 1)->avg('rating'), 2),
        ];

        return view('admin.reviews.index', compact('reviews', 'products', 'stats'));
    }

    /**
     * Cập nhật trạng thái review
     */
    public function updateStatus(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $review->status = $request->status;
        $review->save();

        // Ghi log hoạt động
        try {
            \App\Models\Activity::create([
                'user_id'    => auth()->id(),
                'action'     => 'toggle_review_status',
                'description' => 'Admin đã ' . ($review->status ? 'duyệt' : 'ẩn') . ' review #' . $review->id,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Không thể ghi log Activity: ' . $e->getMessage());
        }

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Cập nhật trạng thái thành công!');
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
                'user_id'    => auth()->id(),
                'action'     => 'delete_review',
                'description' => 'Admin đã xóa review #' . $review->id,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Không thể ghi log Activity: ' . $e->getMessage());
        }

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Xóa review thành công!');
    }
}

