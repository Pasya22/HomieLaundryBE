<?php
// app/Livewire/Invoice/InvoicePrint.php

namespace App\Livewire\Invoice;

use Livewire\Component;
use App\Models\Order;

class InvoicePrint extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        $this->order = $order->load(['customer', 'orderItems.service']);
    }

    public function printInvoice()
    {
        $this->dispatch('print-invoice');
    }

    public function render()
    {
        return view('livewire.invoice.invoice-print');
    }
}
