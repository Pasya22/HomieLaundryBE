<?php
// app/Livewire/Orders/OrderIndex.php

namespace App\Livewire\Orders;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class OrderIndex extends Component
{
    use WithPagination;

    public $search       = '';
    public $statusFilter = '';
    public $dateFilter   = 'today';

    public $statusOptions = [
        'request'   => 'Penerimaan',
        'washing'   => 'Pencucian',
        'drying'    => 'Pengeringan',
        'ironing'   => 'Setrika',
        'ready'     => 'Siap Diambil',
        'completed' => 'Selesai',
    ];

    public $dateOptions = [
        'today'     => 'Hari Ini',
        'yesterday' => 'Kemarin',
        'week'      => 'Minggu Ini',
        'month'     => 'Bulan Ini',
        'all'       => 'Semua',
    ];

    public function render()
    {
        $orders = Order::with(['customer', 'orderItems.service'])
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('order_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->dateFilter, function ($query) {
                return match ($this->dateFilter) {
                    'today'     => $query->whereDate('created_at', today()),
                    'yesterday' => $query->whereDate('created_at', today()->subDay()),
                    'week'      => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month'     => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year),
                    default     => $query
                };
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total'     => Order::when($this->dateFilter, function ($query) {
                return match ($this->dateFilter) {
                    'today'     => $query->whereDate('created_at', today()),
                    'yesterday' => $query->whereDate('created_at', today()->subDay()),
                    'week'      => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month'     => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year),
                    default     => $query
                };
            })->count(),
            'revenue'   => Order::when($this->dateFilter, function ($query) {
                return match ($this->dateFilter) {
                    'today'     => $query->whereDate('created_at', today()),
                    'yesterday' => $query->whereDate('created_at', today()->subDay()),
                    'week'      => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month'     => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year),
                    default     => $query
                };
            })->sum('total_amount'),
            'completed' => Order::where('status', 'completed')
                ->when($this->dateFilter, function ($query) {
                    return match ($this->dateFilter) {
                        'today'     => $query->whereDate('created_at', today()),
                        'yesterday' => $query->whereDate('created_at', today()->subDay()),
                        'week'      => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                        'month'     => $query->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year),
                        default     => $query
                    };
                })->count(),
        ];

        return view('livewire.orders.order-index', compact('orders', 'stats'));
    }

    public function getStatusBadge($status)
    {
        $badges = [
            'request'   => 'bg-blue-100 text-blue-800',
            'washing'   => 'bg-yellow-100 text-yellow-800',
            'drying'    => 'bg-orange-100 text-orange-800',
            'ironing'   => 'bg-purple-100 text-purple-800',
            'ready'     => 'bg-green-100 text-green-800',
            'completed' => 'bg-green-500 text-white',
        ];

        return $badges[$status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getPaymentBadge($status)
    {
        return $status === 'paid'
            ? 'bg-green-100 text-green-800'
            : 'bg-red-100 text-red-800';
    }

    public function deleteOrder(Order $order)
    {
        try {
            // Delete order items first
            $order->orderItems()->delete();
            $order->delete();

            session()->flash('success', 'Order berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus order: ' . $e->getMessage());
        }
    }
}
