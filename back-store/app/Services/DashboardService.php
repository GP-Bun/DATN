<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Product;

class DashboardService
{
    public static function getData(): array
    {
        $year = now()->year;

        /** ===============================
         * TOP STATS
         * =============================== */
        $yearRevenue = (int) DB::table('orders')
            ->whereYear('created_at', $year)
            ->where('payment_status', 'paid')
            ->sum('final_amount');

        $orderCount = DB::table('orders')
            ->whereYear('created_at', $year)
            ->count();

        $customerCount = DB::table('users')
            ->where('role', 'customer')
            ->count();

        $productCount = Product::visible()->count();

        /** ===============================
         * REVENUE LAST 7 DAYS
         * =============================== */
        $daily7Raw = [];
        $daily7Labels = [];
        $maxDay = 1;

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $total = (int) DB::table('orders')
                ->whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('final_amount');

            $daily7Raw[] = $total;
            $daily7Labels[] = $date->format('d/m');
            $maxDay = max($maxDay, $total);
        }

        $daily7 = array_map(
            fn ($v) => round($v / $maxDay * 100),
            $daily7Raw
        );

        /** ===============================
         * REVENUE BY MONTH (12 MONTHS)
         * =============================== */
        $monthlyRaw = [];
        $monthlyLabels = [];
        $maxMonthChart = 1;

        for ($m = 1; $m <= 12; $m++) {
            $total = (int) DB::table('orders')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->where('payment_status', 'paid')
                ->sum('final_amount');

            $monthlyRaw[] = $total;
            $monthlyLabels[] = 'T' . $m;
            $maxMonthChart = max($maxMonthChart, $total);
        }

        $monthly = array_map(
            fn ($v) => round($v / $maxMonthChart * 100),
            $monthlyRaw
        );

        /** ===============================
         * REVENUE LAST 5 YEARS
         * =============================== */
        $yearlyRaw = [];
        $yearlyLabels = [];
        $maxYear = 1;

        for ($y = $year - 4; $y <= $year; $y++) {
            $total = (int) DB::table('orders')
                ->whereYear('created_at', $y)
                ->where('payment_status', 'paid')
                ->sum('final_amount');

            $yearlyRaw[] = $total;
            $yearlyLabels[] = (string) $y;
            $maxYear = max($maxYear, $total);
        }

        $yearly5 = array_map(
            fn ($v) => round($v / $maxYear * 100),
            $yearlyRaw
        );

        /** ===============================
         * CONVERSION RATE
         * =============================== */
        $done = DB::table('orders')
            ->where('order_status', 'delivered')
            ->count();

        $pending = DB::table('orders')
            ->whereIn('order_status', ['pending', 'processing', 'shipped'])
            ->count();

        $conversionTotal = max($done + $pending, 1);

        /** ===============================
         * RECENT ORDERS
         * =============================== */
        $recentOrders = DB::table('orders')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn ($o) => [
                'code'   => 'DH-' . str_pad($o->id, 6, '0', STR_PAD_LEFT),
                'amount' => (int) $o->final_amount,
            ]);

        /** ===============================
         * TOP 3 MONTHS (HIỂN THỊ)
         * =============================== */
        $topMonths = DB::table('orders')
            ->selectRaw('DATE_FORMAT(created_at, "%m/%Y") as month, SUM(final_amount) as total')
            ->whereYear('created_at', $year)
            ->where('payment_status', 'paid')
            ->groupBy('month')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(fn ($m) => [
                'month'  => $m->month,
                'amount' => (int) $m->total,
            ]);

        /** ===============================
         * MAX / MIN MONTH (ĐÚNG NGHIỆP VỤ)
         * =============================== */
        $monthTotals = DB::table('orders')
            ->selectRaw('MONTH(created_at) as m, SUM(final_amount) as total')
            ->whereYear('created_at', $year)
            ->where('payment_status', 'paid')
            ->groupBy('m')
            ->pluck('total')
            ->map(fn ($v) => (int) $v);

        $maxMonthRevenue = $monthTotals->max() ?? 0;
        $minMonthRevenue = $monthTotals->min() ?? 0;

        /** ===============================
         * GLOBAL STATS - TOP SẢN PHẨM TẤT CẢ THỜI GIAN
         * =============================== */
        // Tổng số đơn hàng tất cả thời gian
        $ordersTotal = DB::table('orders')->count();
        // Tổng số đơn đã thanh toán
        $ordersPaidTotal = DB::table('orders')->where('payment_status', 'paid')->count();

        // Top sản phẩm bán chạy nhất (có qty > 0)
        $topBestProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->having('total_sold', '>', 0)
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'total_sold' => (int) $p->total_sold,
            ]);

        // Top sản phẩm không bán chạy (bán ít nhất hoặc không bán)
        $topWorstProducts = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->select('products.id', 'products.name', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'total_sold' => (int) $p->total_sold,
            ]);

        /** ===============================
         * RETURN DATA
         * =============================== */
        return [
            'topStats' => [
                'yearRevenue' => $yearRevenue,
                'orderCount' => $orderCount,
                'customerCount' => $customerCount,
                'productCount' => $productCount,
                'orderAvgPerMonth' => round($orderCount / 12),
                'customerGrowthPercent' => 8.1,
            ],
            'chart' => [
                'daily7' => [
                    'percent' => $daily7,
                    'raw' => $daily7Raw,
                    'labels' => $daily7Labels,
                ],
                'monthly' => [
                    'percent' => $monthly,
                    'raw' => $monthlyRaw,
                    'labels' => $monthlyLabels,
                ],
                'yearly5' => [
                    'percent' => $yearly5,
                    'raw' => $yearlyRaw,
                    'labels' => $yearlyLabels,
                ],
            ],
            'conversion' => [
                'done' => round($done / $conversionTotal * 100),
                'pending' => 100 - round($done / $conversionTotal * 100),
            ],
            'recentOrders' => $recentOrders,
            'topMonths' => $topMonths,
            'quickStats' => [
                'avgDailyRevenue' => round($yearRevenue / 365),
                'avgOrderValue' => $orderCount
                    ? round($yearRevenue / $orderCount)
                    : 0,
                'maxMonth' => $maxMonthRevenue,
                'minMonth' => $minMonthRevenue,
            ],
            'globalStats' => [
                'ordersTotal' => $ordersTotal,
                'ordersPaidTotal' => $ordersPaidTotal,
                'topBestProducts' => $topBestProducts,
                'topWorstProducts' => $topWorstProducts,
            ],
        ];
    }
}
