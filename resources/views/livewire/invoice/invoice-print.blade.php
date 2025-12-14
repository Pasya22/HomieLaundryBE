<!-- resources/views/livewire/invoice/invoice-print.blade.php -->
<div class="bg-white text-black thermal-invoice" x-data x-init="window.addEventListener('print-invoice', () => { window.print(); })">
    <!-- Thermal Paper Style -->
    <div class="max-w-80 mx-auto p-4 thermal-content">

        <!-- Header dengan Logo -->
        <div class="text-center border-b border-dashed border-gray-400 pb-3 mb-3">
            <!-- Logo Homie Laundry -->
            <div class="flex justify-center items-center mb-2">
                <div class="w-12 h-12 bg-blue-600 text-white rounded flex items-center justify-center font-bold text-lg">
                    <i class="fas fa-tshirt"></i>
                </div>
            </div>
            <h1 class="text-xl font-bold uppercase tracking-tight">HOMIE LAUNDRY</h1>
            <p class="text-xs text-gray-600">Jalan Sukagalih I Aspol No 4</p>
            <p class="text-xs text-gray-600">Telp: 0812-3456-7890</p>
        </div>

        <!-- Info Order -->
        <div class="space-y-1 text-sm mb-3">
            <div class="flex justify-between">
                <span class="font-medium">No. Order:</span>
                <span class="font-bold">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Customer:</span>
                <span class="text-right">{{ $order->customer->name }}</span>
            </div>
            @if($order->customer->phone)
            <div class="flex justify-between">
                <span>Telepon:</span>
                <span>{{ $order->customer->phone }}</span>
            </div>
            @endif
        </div>

        <!-- Garis Pembatas -->
        <div class="border-t border-dashed border-gray-400 my-2"></div>

        <!-- Detail Layanan -->
        <div class="mb-3">
            <h3 class="text-sm font-bold uppercase text-center mb-2">DETAIL LAYANAN</h3>
            <div class="space-y-2">
                @foreach($order->orderItems as $item)
                <div class="text-xs">
                    <div class="flex justify-between font-medium">
                        <span>{{ $item->service->name }}</span>
                        <span>{{ $item->quantity }}x</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span class="flex-1">{{ $item->service->category }}</span>
                        <span class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                    </div>
                    @if($item->service->duration)
                    <div class="text-gray-500 text-xs">Durasi: {{ $item->service->duration }}</div>
                    @endif
                    @if($item->notes)
                    <div class="text-blue-600 text-xs">Note: {{ $item->notes }}</div>
                    @endif
                    <div class="flex justify-between font-bold border-t border-dotted border-gray-300 pt-1 mt-1">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Garis Pembatas -->
        <div class="border-t border-dashed border-gray-400 my-2"></div>

        <!-- Total Pembayaran -->
        <div class="mb-3">
            <div class="flex justify-between text-sm font-bold">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span>Status Bayar:</span>
                <span class="font-bold {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span>Metode Bayar:</span>
                <span>{{ $order->payment_method === 'cash' ? 'TUNAI' : 'TRANSFER' }}</span>
            </div>
        </div>

        <!-- Estimasi Selesai -->
        @if($order->estimated_completion)
        <div class="mb-3 p-2 bg-yellow-50 border border-yellow-200 rounded text-center">
            <div class="text-xs font-bold text-yellow-700">ESTIMASI SELESAI</div>
            <div class="text-sm font-bold">{{ $order->estimated_completion->format('d/m/Y H:i') }}</div>
        </div>
        @endif

        <!-- Garis Pembatas -->
        <div class="border-t border-dashed border-gray-400 my-2"></div>

        <!-- Syarat & Ketentuan -->
        <div class="text-xs text-gray-600 mb-3">
            <div class="font-bold text-center mb-1">SYARAT & KETENTUAN</div>
            <div class="space-y-1">
                <div>1. Simpan nota ini untuk pengambilan</div>
                <div>2. Klaim kehilangan max 24 jam setelah pengambilan</div>
                <div>3. Barang yang tidak diambil dalam 30 hari menjadi hak milik laundry</div>
                <div>4. Tidak menerima barang yang sobek/lusuh sebelumnya</div>
                <div>5. Cuaca hujan dapat mempengaruhi waktu pengeringan</div>
            </div>
        </div>

        <!-- Catatan Order -->
        @if($order->notes)
        <div class="mb-3 p-2 bg-blue-50 border border-blue-200 rounded">
            <div class="text-xs font-bold text-blue-700">CATATAN ORDER</div>
            <div class="text-sm">{{ $order->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center text-xs text-gray-500 border-t border-dashed border-gray-400 pt-2">
            <div class="font-bold">TERIMA KASIH ATAS KEPERCAYAAN ANDA</div>
            <div>*** Nota ini sah sebagai bukti pemesanan ***</div>
        </div>

        <!-- Barcode Area (Optional) -->
        <div class="text-center mt-3">
            <div class="inline-block border border-dashed border-gray-400 p-2">
                <div class="text-xs text-gray-500">[BARCODE AREA]</div>
                <div class="text-xs font-mono">{{ $order->order_number }}</div>
            </div>
        </div>
    </div>

    <!-- Print Button (Visible hanya di screen) -->
    <div class="text-center mt-6 no-print">
        <button wire:click="printInvoice"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
            <i class="fas fa-print mr-2"></i>Print Nota
        </button>
        <a href="{{ route('orders.show', $order) }}"
           class="ml-3 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
    <style>
    /* Thermal Printer Style */
    .thermal-invoice {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        line-height: 1.2;
    }

    .thermal-content {
        width: 80mm; /* Lebar kertas thermal */
        min-height: 100vh;
    }

    /* Print Styles */
    @media print {
        @page {
            margin: 0;
            padding: 0;
            size: 80mm auto; /* Thermal paper width */
        }

        body {
            margin: 0;
            padding: 0;
            background: white;
            font-size: 10px;
        }

        .thermal-invoice {
            width: 80mm;
            margin: 0;
            padding: 5mm;
            background: white;
            font-size: 10px;
        }

        .thermal-content {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .no-print {
            display: none !important;
        }

        /* Optimasi untuk thermal printer */
        * {
            color: black !important;
            background: transparent !important;
        }

        /* Pastikan border tetap visible saat print */
        .border-dashed {
            border-style: dashed !important;
        }

        .border-dotted {
            border-style: dotted !important;
        }
    }

    /* Screen Styles */
    @media screen {
        .thermal-invoice {
            background: #f5f5f5;
            min-height: 100vh;
            padding: 20px 0;
        }

        .thermal-content {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 20px;
            margin: 0 auto;
        }
    }

    /* Thermal specific optimizations */
    .thermal-invoice {
        color: #000 !important;
    }

    .thermal-invoice .bg-blue-600 {
        background-color: #000 !important;
        color: #fff !important;
    }

    .thermal-invoice .bg-yellow-50,
    .thermal-invoice .bg-blue-50 {
        background-color: #f8f8f8 !important;
        border-color: #ccc !important;
    }

    /* Font optimization for thermal */
    .thermal-content {
        font-family: 'Courier New', 'Monaco', 'Menlo', monospace;
        font-weight: normal;
        letter-spacing: 0;
    }

    /* Thermal printer friendly colors */
    @media print {
        .text-blue-600 { color: #000 !important; }
        .text-green-600 { color: #000 !important; }
        .text-red-600 { color: #000 !important; }
        .text-yellow-700 { color: #000 !important; }
        .text-gray-600 { color: #444 !important; }
        .text-gray-500 { color: #666 !important; }

        .bg-yellow-50 { background: #f0f0f0 !important; }
        .bg-blue-50 { background: #f0f0f0 !important; }
    }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        // Auto print ketika component di-load (optional)
        // window.print();
    });

    // Fallback untuk browser yang tidak support window.print event
    document.addEventListener('DOMContentLoaded', function() {
        const printButton = document.querySelector('[wire\\:click="printInvoice"]');
        if (printButton) {
            printButton.addEventListener('click', function() {
                window.print();
            });
        }
    });
    </script>
</div>

