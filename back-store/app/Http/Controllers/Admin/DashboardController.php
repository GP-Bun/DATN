<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // Nếu là Staff, chỉ hiển thị dashboard đơn giản (không có thống kê doanh thu)
        if (!$isAdmin) {
            return view('admin.dashboard-staff');
        }

        // Còn lại là Admin - hiển thị đầy đủ thống kê
        $defaultData = [
            'topStats' => [
                'yearRevenue' => 486000000,
                'orderCount' => 802,
                'customerCount' => 312,
                'productCount' => 58,
                'orderAvgPerMonth' => 67,
                'customerGrowthPercent' => 8.1,
            ],
            'chart' => [
                'daily7' => [
                    'percent' => [20, 35, 30, 50, 45, 60, 70],
                    'raw' => [2000000, 3500000, 3000000, 5000000, 4500000, 6000000, 7000000],
                    'labels' => ['06/01', '07/01', '08/01', '09/01', '10/01', '11/01', '12/01'],
                ],
                'monthly' => [
                    'percent' => [25, 30, 28, 35, 40, 38, 42, 45, 48, 52, 50, 55],
                    'raw' => [25000000, 30000000, 28000000, 35000000, 40000000, 38000000, 42000000, 45000000, 48000000, 52000000, 50000000, 55000000],
                    'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                ],
                'yearly5' => [
                    'percent' => [40, 55, 60, 70, 85],
                    'raw' => [200000000, 275000000, 300000000, 350000000, 425000000],
                    'labels' => ['2022', '2023', '2024', '2025', '2026'],
                ],
            ],
            'conversion' => [
                'done' => 68,
                'pending' => 32,
            ],
            'recentOrders' => [
                ['code' => 'DH-251201', 'amount' => 1290000],
                ['code' => 'DH-251202', 'amount' => 850000],
                ['code' => 'DH-251203', 'amount' => 640000],
                ['code' => 'DH-251204', 'amount' => 2150000],
            ],
            'topMonths' => [
                ['month' => '11/2025', 'amount' => 55000000],
                ['month' => '10/2025', 'amount' => 52000000],
                ['month' => '09/2025', 'amount' => 48000000],
            ],
            'quickStats' => [
                'avgDailyRevenue' => 1330000,
                'avgOrderValue' => 606000,
                'maxMonth' => 55000000,
                'minMonth' => 25000000,
            ],
        ];

        try {
            $serviceData = DashboardService::getData();

            $hasRealData = !empty($serviceData['topStats']['orderCount']) && $serviceData['topStats']['orderCount'] > 0;

            $data = $defaultData;

            if ($hasRealData) {
                $data['topStats'] = array_merge($data['topStats'], $serviceData['topStats']);

                foreach (['daily7', 'monthly', 'yearly5'] as $chartType) {
                    if (!empty($serviceData['chart'][$chartType]['percent']) &&
                        array_sum($serviceData['chart'][$chartType]['percent']) > 0) {
                        $data['chart'][$chartType] = $serviceData['chart'][$chartType];
                    }
                }

                if (!empty($serviceData['conversion'])) {
                    $data['conversion'] = $serviceData['conversion'];
                }

                if (!empty($serviceData['recentOrders']) && count($serviceData['recentOrders']) > 0) {
                    $data['recentOrders'] = $serviceData['recentOrders']->toArray();
                }

                if (!empty($serviceData['topMonths']) && count($serviceData['topMonths']) > 0) {
                    $data['topMonths'] = $serviceData['topMonths']->toArray();
                }

                if (!empty($serviceData['quickStats'])) {
                    $data['quickStats'] = array_merge($data['quickStats'], $serviceData['quickStats']);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            $data = $defaultData;
        }

        return view('admin.dashboard', compact('data'));
    }
}
