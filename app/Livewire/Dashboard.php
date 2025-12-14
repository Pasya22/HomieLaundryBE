<?php
// app/Livewire/Dashboard.php

namespace App\Livewire;

use App\Models\Order;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public $todayOrders;
    public $todayRevenue;
    public $completedToday;
    public $inProgress;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $today = Carbon::today();

        $this->todayOrders    = Order::whereDate('created_at', $today)->count();
        $this->todayRevenue   = Order::whereDate('created_at', $today)->sum('total_amount');
        $this->completedToday = Order::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();
        $this->inProgress = Order::whereDate('created_at', $today)
            ->whereIn('status', ['process', 'washing', 'drying', 'ironing'])
            ->count();
    }

    public function getRecentOrdersProperty()
    {
        return Order::with('customer')
            ->latest()
            ->take(5)
            ->get();
    }

    public function getStatusBadge($status)
    {
        $badges = [
            'request'   => 'status-new',
            'washing'   => 'status-washing',
            'drying'    => 'status-drying',
            'ironing'   => 'status-ironing',
            'ready'     => 'status-ready',
            'completed' => 'status-ready',
        ];

        return $badges[$status] ?? 'status-new';
    }

    public function getStatusText($status)
    {
        $texts = [
            'request'   => 'Baru',
            'washing'   => 'Cuci',
            'drying'    => 'Kering',
            'ironing'   => 'Setrika',
            'ready'     => 'Siap',
            'completed' => 'Selesai',
        ];

        return $texts[$status] ?? 'Baru';
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'recentOrders' => $this->recentOrders,
        ]);
    }
}
