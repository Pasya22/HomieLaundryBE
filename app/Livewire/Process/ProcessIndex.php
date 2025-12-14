<?php

namespace App\Livewire\Process;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;

class ProcessIndex extends Component
{
    use WithPagination;
  public $search = '';
    public $statusFilter = '';
    public $selectedOrder = null;
    public $showUpdateModal = false;

    // Update form
    public $newStatus = '';
    public $processNotes = '';
    public $estimatedCompletion = '';

    public $statusOptions = [
        'request' => 'Penerimaan',
        'washing' => 'Pencucian',
        'drying' => 'Pengeringan',
        'ironing' => 'Setrika',
        'ready' => 'Siap Diambil',
        'completed' => 'Selesai'
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
            ->where('status', '!=', 'completed')
            // FIX: PostgreSQL compatible ordering
            ->orderByRaw("
                CASE
                    WHEN status = 'request' THEN 1
                    WHEN status = 'washing' THEN 2
                    WHEN status = 'drying' THEN 3
                    WHEN status = 'ironing' THEN 4
                    WHEN status = 'ready' THEN 5
                    ELSE 6
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.process.process-index', compact('orders'));
    }

    public function selectOrder(Order $order)
    {
        $this->selectedOrder = $order;
        $this->newStatus = $order->status;
        $this->processNotes = '';
        $this->estimatedCompletion = $order->estimated_completion?->format('Y-m-d\TH:i') ?? now()->addHours(6)->format('Y-m-d\TH:i');
        $this->showUpdateModal = true;
    }

    public function updateOrderStatus()
    {
        $this->validate([
            'newStatus' => 'required|in:request,washing,drying,ironing,ready,completed',
            'processNotes' => 'nullable|string|max:500',
            'estimatedCompletion' => 'nullable|date'
        ]);

        try {
            $this->selectedOrder->update([
                'status' => $this->newStatus,
                'estimated_completion' => $this->estimatedCompletion,
            ]);

            // Add to order notes if process notes provided
            if ($this->processNotes) {
                $currentNotes = $this->selectedOrder->notes ? $this->selectedOrder->notes . "\n" : '';
                $this->selectedOrder->update([
                    'notes' => $currentNotes . '[' . now()->format('d/m/Y H:i') . '] ' . $this->processNotes
                ]);
            }

            $this->showUpdateModal = false;
            $this->selectedOrder = null;

            session()->flash('success', 'Status order berhasil diperbarui!');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    public function markAsReady(Order $order)
    {
        try {
            $order->update(['status' => 'ready']);
            session()->flash('success', 'Order ditandai sebagai SIAP DIAMBIL!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    public function markAsCompleted(Order $order)
    {
        try {
            $order->update(['status' => 'completed']);
            session()->flash('success', 'Order ditandai sebagai SELESAI!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    public function getStatusBadge($status)
    {
        $badges = [
            'request' => 'bg-blue-100 text-blue-800',
            'washing' => 'bg-yellow-100 text-yellow-800',
            'drying' => 'bg-orange-100 text-orange-800',
            'ironing' => 'bg-purple-100 text-purple-800',
            'ready' => 'bg-green-100 text-green-800',
            'completed' => 'bg-green-500 text-white'
        ];

        return $badges[$status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusIcon($status)
    {
        $icons = [
            'request' => 'fas fa-inbox',
            'washing' => 'fas fa-soap',
            'drying' => 'fas fa-wind',
            'ironing' => 'fas fa-tshirt',
            'ready' => 'fas fa-check-circle',
            'completed' => 'fas fa-flag-checkered'
        ];

        return $icons[$status] ?? 'fas fa-cog';
    }

    public function getNextStatus($currentStatus)
    {
        $statusOrder = array_keys($this->statusOptions);
        $currentIndex = array_search($currentStatus, $statusOrder);

        return $currentIndex < count($statusOrder) - 1 ? $statusOrder[$currentIndex + 1] : null;
    }

    public function quickUpdateStatus(Order $order, $newStatus)
    {
        try {
            $order->update(['status' => $newStatus]);
            session()->flash('success', 'Status order berhasil diperbarui ke: ' . $this->statusOptions[$newStatus]);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }
}
