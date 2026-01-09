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
        $maxDay = 1;

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $total = (int) DB::table('orders')
                ->whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('final_amount');

            $daily7Raw[] = $total;
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
        $maxMonthChart = 1;

        for ($m = 1; $m <= 12; $m++) {
            $total = (int) DB::table('orders')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->where('payment_status', 'paid')
                ->sum('final_amount');

            $monthlyRaw[] = $total;
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
        $maxYear = 1;

        for ($y = $year - 4; $y <= $year; $y++) {
            $total = (int) DB::table('orders')
                ->whereYear('created_at', $y)
                ->where('payment_status', 'paid')
                ->sum('final_amount');

            $yearlyRaw[] = $total;
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
            'chart' => compact('daily7', 'monthly', 'yearly5'),
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
        ];
    }
}
