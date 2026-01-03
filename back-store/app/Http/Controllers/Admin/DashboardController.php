<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
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
                'monthlyRevenue' => [25,30,28,35,40,38,42,45,48,52,50,55],
            ],
            'conversion' => [
                'done' => 68,
                'pending' => 32,
            ],
            'recentOrders' => [
                ['code'=>'DH-251201','amount'=>1290000],
                ['code'=>'DH-251202','amount'=>850000],
                ['code'=>'DH-251203','amount'=>640000],
                ['code'=>'DH-251204','amount'=>2150000],
            ],
            'topMonths' => [
                ['month'=>'11/2025','amount'=>55000000],
                ['month'=>'10/2025','amount'=>52000000],
                ['month'=>'09/2025','amount'=>48000000],
            ],
            'quickStats' => [
                'avgDailyRevenue'=>1330000,
                'avgOrderValue'=>606000,
                'maxMonth'=>55000000,
                'minMonth'=>25000000,
            ],
        ];

        try {
            $response = Http::get(url('/api/admin/dashboard'))->json();
            if (($response['topStats']['orderCount'] ?? 0) > 0) {
                $data = array_replace_recursive($defaultData, $response);
            } else {
                $data = $defaultData;
            }
        } catch (\Exception $e) {
            $data = $defaultData;
        }

        return view('admin.dashboard', compact('data'));
    }
}
