<?php
// app/Livewire/Orders/ShowOrder.php

namespace App\Livewire\Orders;

use App\Models\Order;
use Livewire\Component;

class ShowOrder extends Component
{
    public Order $order;
    public $showConfirmPayment   = false;
    public $confirmPaymentAmount = 0;
    public $statusHistory        = [];
    public $currentStatusIndex;

    // Status progression
    public $statusSteps = [
        'request'   => ['label' => 'Penerimaan', 'icon' => 'fas fa-inbox', 'color' => 'gray'],
        'washing'   => ['label' => 'Pencucian', 'icon' => 'fas fa-soap', 'color' => 'blue'],
        'drying'    => ['label' => 'Pengeringan', 'icon' => 'fas fa-wind', 'color' => 'yellow'],
        'ironing'   => ['label' => 'Setrika', 'icon' => 'fas fa-tshirt', 'color' => 'purple'],
        'ready'     => ['label' => 'Siap Diambil', 'icon' => 'fas fa-check-circle', 'color' => 'green'],
        'completed' => ['label' => 'Selesai', 'icon' => 'fas fa-flag-checkered', 'color' => 'green'],
    ];

    public function mount(Order $order)
    {
        $this->order = $order->load(['customer', 'orderItems.service']);
        $this->loadStatusHistory();
        $this->calculateCurrentStatusIndex();
    }
    public function loadStatusHistory()
    {
        // Simulate status history (in real app, this would come from a status_logs table)
        $this->statusHistory = [
            [
                'status'      => 'request',
                'label'       => 'Order Diterima',
                'description' => 'Order telah diterima dan masuk antrian',
                'timestamp'   => $this->order->created_at,
                'user'        => 'System',
            ],
            [
                'status'      => 'washing',
                'label'       => 'Mulai Pencucian',
                'description' => 'Proses pencucian dimulai',
                'timestamp'   => $this->order->created_at->addMinutes(30),
                'user'        => 'Staff Laundry',
            ],
        ];

        // Add current status if not in history
        if ($this->order->status !== 'request' && ! collect($this->statusHistory)->contains('status', $this->order->status)) {
            $this->statusHistory[] = [
                'status'      => $this->order->status,
                'label'       => $this->statusSteps[$this->order->status]['label'] . ' Dimulai',
                'description' => 'Proses ' . strtolower($this->statusSteps[$this->order->status]['label']) . ' sedang berjalan',
                'timestamp'   => now(),
                'user'        => 'System',
            ];
        }
    }

    public function calculateCurrentStatusIndex()
    {
        $statusOrder              = array_keys($this->statusSteps);
        $this->currentStatusIndex = array_search($this->order->status, $statusOrder);
    }

    public function updateStatus($newStatus)
    {
        $currentIndex = array_search($this->order->status, array_keys($this->statusSteps));
        $newIndex     = array_search($newStatus, array_keys($this->statusSteps));

        // Validasi: tidak bisa skip step
        if ($newIndex > $currentIndex + 1) {
            $nextStep      = array_keys($this->statusSteps)[$currentIndex + 1];
            $nextStepLabel = $this->statusSteps[$nextStep]['label'];

            session()->flash('error', "Tidak bisa melompat ke {$this->statusSteps[$newStatus]['label']}. Harus melalui {$nextStepLabel} terlebih dahulu.");
            return;
        }

        try {
            $this->order->update(['status' => $newStatus]);

            // Send WhatsApp notification jika status ready
            if ($newStatus === 'ready' && ! $this->order->ready_notification_sent) {
                $whatsappService = new \App\Services\WhatsAppService();
                $whatsappService->sendReadyNotification($this->order);
            }

            // Add to status history
            $this->statusHistory[] = [
                'status'      => $newStatus,
                'label'       => 'Status Diperbarui: ' . $this->statusSteps[$newStatus]['label'],
                'description' => 'Order dipindahkan ke tahap ' . strtolower($this->statusSteps[$newStatus]['label']),
                'timestamp'   => now(),
                'user'        => auth()->user()->name ?? 'Staff',
            ];

            $this->calculateCurrentStatusIndex();

            session()->flash('success', 'Status order berhasil diperbarui ke: ' . $this->statusSteps[$newStatus]['label']);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    public function markAsCompleted()
    {
        // Cek apakah semua proses sudah selesai
        if ($this->order->status !== 'ready') {
            session()->flash('error', 'Order belum siap untuk diselesaikan! Pastikan status sudah "Siap Diambil" terlebih dahulu.');
            return;
        }

        try {
            $this->order->update(['status' => 'completed']);

            // Add to status history
            $this->statusHistory[] = [
                'status'      => 'completed',
                'label'       => 'Order Selesai',
                'description' => 'Order telah selesai dan diambil oleh customer',
                'timestamp'   => now(),
                'user'        => auth()->user()->name ?? 'Staff',
            ];

            $this->calculateCurrentStatusIndex();

            session()->flash('success', 'Order berhasil ditandai sebagai SELESAI!');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menandai order sebagai selesai: ' . $e->getMessage());
        }
    }
    public function confirmMarkAsPaid()
    {
        $this->confirmPaymentAmount = $this->order->total_amount;
        $this->showConfirmPayment   = true;
    }
    public function markAsPaid()
    {
        try {
            $this->order->update(['payment_status' => 'paid']);
            $this->showConfirmPayment = false;

            session()->flash('success', 'Status pembayaran diperbarui menjadi LUNAS!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui status pembayaran: ' . $e->getMessage());
        }
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

    public function getNextStatus()
    {
        $statusOrder  = array_keys($this->statusSteps);
        $currentIndex = array_search($this->order->status, $statusOrder);

        return $currentIndex < count($statusOrder) - 1 ? $statusOrder[$currentIndex + 1] : null;
    }

    public function render()
    {
        return view('livewire.orders.show-order');
    }
}
