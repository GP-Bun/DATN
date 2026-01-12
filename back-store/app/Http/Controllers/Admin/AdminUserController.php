<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Danh sách tất cả user (có phân trang)
     */
    public function index()
    {
        $users = User::paginate(10);

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Chi tiết một user kèm địa chỉ, đơn hàng, reviews, giỏ hàng
     */
    public function show($id)
    {
        $user = User::with([
            'addresses',
            'orders.items.product',
            'reviews.product',
            'cart.items.product',
            'activities'
        ])->findOrFail($id);

        return response()->json([
            'user' => $user,
            'addresses' => $user->addresses,
            'orders' => $user->orders,
            'reviews' => $user->reviews,
            'cart' => $user->cart,
            'cart_total' => $user->cart_total,
        ]);
    }

    /**
     * Tất cả đơn hàng của user
     */
    public function orders($id)
    {
        $user = User::findOrFail($id);
        $orders = $user->orders()->with(['items.product'])->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'orders' => $orders
        ]);
    }

    /**
     * Tất cả reviews/comments của user
     */
    public function reviews($id)
    {
        $user = User::findOrFail($id);
        $reviews = $user->reviews()->with('product')->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'reviews' => $reviews
        ]);
    }

    /**
     * Timeline hoạt động (orders + reviews) sắp xếp theo thời gian
     */
    public function timeline(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Nhận tham số filter: all | orders | reviews | activities
        $filter = $request->input('filter', 'all');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $orders = collect();
        $reviews = collect();
        $activitiesLog = collect();

        if ($filter === 'all' || $filter === 'orders') {
            $orders = $user->orders()
                ->with('items.product')
                ->select('id', 'status', 'total_amount', 'created_at')
                ->get()
                ->map(function ($order) {
                    return [
                        'type' => 'order',
                        'id' => $order->id,
                        'status' => $order->status,
                        'total' => $order->total_amount,
                        'created_at' => $order->created_at,
                    ];
                });
        }

        if ($filter === 'all' || $filter === 'reviews') {
            $reviews = $user->reviews()
                ->with('product:id,name')
                ->select('id', 'product_id', 'comment', 'rating', 'created_at')
                ->get()
                ->map(function ($review) {
                    return [
                        'type' => 'review',
                        'id' => $review->id,
                        'product' => $review->product->name ?? null,
                        'content' => $review->comment,
                        'rating' => $review->rating,
                        'created_at' => $review->created_at,
                    ];
                });
        }

        if ($filter === 'all' || $filter === 'activities') {
            $activitiesLog = $user->activities()
                ->select('id', 'action', 'description', 'created_at')
                ->get()
                ->map(function ($activity) {
                    return [
                        'type' => 'activity',
                        'id' => $activity->id,
                        'action' => $activity->action,
                        'description' => $activity->description,
                        'created_at' => $activity->created_at,
                    ];
                });
        }

        // Merge và sort
        $activities = $orders->merge($reviews)->merge($activitiesLog)->sortByDesc('created_at')->values();

        // Phân trang thủ công
        $paged = $activities->forPage($page, $perPage);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'timeline' => $paged->values(),
            'pagination' => [
                'total' => $activities->count(),
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($activities->count() / $perPage),
            ]
        ]);
    }



    /**
     * Cập nhật trạng thái active của user
     */
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->active = $request->input('active', $user->active);
        $user->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'user' => $user
        ]);
    }

    /**
     * Cập nhật role của user
     */
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $role = $request->input('role');
        if (!in_array($role, ['admin', 'customer'])) {
            return response()->json(['message' => 'Role không hợp lệ'], 422);
        }

        $user->role = $role;
        $user->save();

        return response()->json([
            'message' => 'Cập nhật role thành công',
            'user' => $user
        ]);
    }

    /**
     * Xoá mềm user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User đã được xoá mềm',
        ]);
    }

    /**
     * Khôi phục user đã xoá mềm
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'message' => 'User đã được khôi phục',
            'user' => $user
        ]);
    }
}
