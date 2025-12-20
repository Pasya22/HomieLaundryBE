<?php
// app/Http/Controllers/Api/DashboardController.php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $today = Carbon::today();

        $stats = [
            'todayOrders'    => Order::whereDate('created_at', $today)->count(),
            'todayRevenue'   => Order::whereDate('created_at', $today)->sum('total_amount'),
            'completedToday' => Order::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->count(),
            'inProgress' => Order::whereDate('created_at', $today)
                ->whereIn('status', ['request', 'washing', 'drying', 'ironing', 'ready'])
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function recentOrders(Request $request)
    {
        $orders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders)
        ]);
    }

    public function dashboardData(Request $request)
    {
        $today = Carbon::today();

        $stats = [
            'todayOrders'    => Order::whereDate('created_at', $today)->count(),
            'todayRevenue'   => Order::whereDate('created_at', $today)->sum('total_amount'),
            'completedToday' => Order::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->count(),
            'inProgress' => Order::whereDate('created_at', $today)
                ->whereIn('status', ['request', 'washing', 'drying', 'ironing', 'ready'])
                ->count(),
        ];

        $recentOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recentOrders' => OrderResource::collection($recentOrders)
            ]
        ]);
    }
}
