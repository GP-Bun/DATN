<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $year = now()->year;

        /** ===============================
         * TOP STATS
         * =============================== */
        $yearRevenue = DB::table('orders')
            ->whereYear('created_at', $year)
            ->where('payment_status', 'paid')
            ->sum('final_amount');

        $orderCount = DB::table('orders')
            ->whereYear('created_at', $year)
            ->count();

        $customerCount = DB::table('users')
            ->where('role', 'customer')
            ->count();

        $productCount = DB::table('products')
            ->where('status', 'active')
            ->count();

        /** ===============================
         * MONTHLY REVENUE (BAR CHART)
         * =============================== */
        $monthlyRevenue = [];

        for ($m = 1; $m <= 12; $m++) {
            $total = DB::table('orders')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->where('payment_status', 'paid')
                ->sum('final_amount');

            // đổi sang % (demo chart)
            $monthlyRevenue[] = $yearRevenue > 0
                ? round(($total / $yearRevenue) * 100)
                : 0;
        }

        /** ===============================
         * CONVERSION RATE
         * =============================== */
        $doneOrders = DB::table('orders')
            ->where('order_status', 'completed')
            ->count();

        $pendingOrders = DB::table('orders')
            ->where('order_status', 'pending')
            ->count();

        $totalOrders = max($doneOrders + $pendingOrders, 1);

        $donePercent = round(($doneOrders / $totalOrders) * 100);
        $pendingPercent = 100 - $donePercent;

        /** ===============================
         * RECENT ORDERS
         * =============================== */
        $recentOrders = DB::table('orders')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get()
            ->map(function ($o) {
                return [
                    'code' => 'DH-' . str_pad($o->id, 6, '0', STR_PAD_LEFT),
                    'amount' => (int) $o->final_amount
                ];
            });

        /** ===============================
         * TOP MONTHS
         * =============================== */
        $topMonths = DB::table('orders')
            ->selectRaw('DATE_FORMAT(created_at, "%m/%Y") as month, SUM(final_amount) as total')
            ->where('payment_status', 'paid')
            ->groupBy('month')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(fn ($m) => [
                'month' => $m->month,
                'amount' => (int) $m->total
            ]);

        /** ===============================
         * QUICK STATS
         * =============================== */
        $avgOrderValue = $orderCount > 0
            ? round($yearRevenue / $orderCount)
            : 0;

        $avgDailyRevenue = round($yearRevenue / 365);

        return response()->json([
            'topStats' => [
                'yearRevenue' => (int) $yearRevenue,
                'orderCount' => $orderCount,
                'customerCount' => $customerCount,
                'productCount' => $productCount,
                'orderAvgPerMonth' => round($orderCount / 12),
                'customerGrowthPercent' => 8.1 // demo
            ],
            'chart' => [
                'monthlyRevenue' => $monthlyRevenue
            ],
            'conversion' => [
                'done' => $donePercent,
                'pending' => $pendingPercent
            ],
            'recentOrders' => $recentOrders,
            'topMonths' => $topMonths,
            'quickStats' => [
                'avgDailyRevenue' => $avgDailyRevenue,
                'avgOrderValue' => $avgOrderValue,
                'maxMonth' => $topMonths->first()['amount'] ?? 0,
                'minMonth' => $topMonths->last()['amount'] ?? 0
            ]
        ]);
    }


}
