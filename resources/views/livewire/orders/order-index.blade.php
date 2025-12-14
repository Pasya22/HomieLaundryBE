<!-- resources/views/livewire/orders/order-index.blade.php -->
<div>
    <div wire:poll.1s>

        <!-- Page Title -->
        <div class="flex items-center gap-3 mb-6">
            <i class="fas fa-list-alt text-blue-500 text-xl"></i>
            <h1 class="text-2xl font-bold text-blue-700">Daftar Order</h1>
            {{-- <a href="{{ route('invoice.print', ['order' => $order->id]) }}" target="_blank"
                class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                <i class="fas fa-receipt mr-1"></i>Nota
            </a> --}}
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-lg p-4 text-center shadow-sm border">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                <div class="text-xs text-gray-600">Total Order</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center shadow-sm border">
                <div class="text-lg font-bold text-green-600">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-600">Pendapatan</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center shadow-sm border">
                <div class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</div>
                <div class="text-xs text-gray-600">Selesai</div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="mobile-card">
            <!-- Search -->
            <div class="relative mb-4">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari order (No. Order / Nama Pelanggan)..."
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-2 gap-3">
                <!-- Date Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                    <select wire:model.live="dateFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @foreach ($dateOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model.live="statusFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Create New Order Button -->
        <a href="{{ route('orders.create') }}"
            class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg mb-4 transition duration-200 text-center">
            <i class="fas fa-plus-circle mr-2"></i>Buat Order Baru
        </a>

        <!-- Orders List -->
        <div class="space-y-3">
            @forelse($orders as $order)
                <div class="mobile-card">
                    <!-- Order Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900">{{ $order->order_number }}</h3>
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium {{ $this->getStatusBadge($order->status) }}">
                                    {{ $statusOptions[$order->status] }}
                                </span>
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium {{ $this->getPaymentBadge($order->payment_status) }}">
                                    {{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">{{ $order->customer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-600">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Order Items Preview -->
                    <div class="mb-3">
                        @foreach ($order->orderItems->take(2) as $item)
                            <div
                                class="flex justify-between text-sm py-1 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                                <span class="text-gray-600 truncate flex-1 mr-2">
                                    {{ $item->service->name }} ({{ $item->quantity }}x)
                                </span>
                                <span class="font-medium whitespace-nowrap">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                        @if ($order->orderItems->count() > 2)
                            <div class="text-xs text-gray-500 text-center mt-1">
                                +{{ $order->orderItems->count() - 2 }} layanan lainnya
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-3 border-t border-gray-200">
                        <a href="{{ route('orders.show', $order) }}"
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-3 rounded text-sm font-medium transition duration-200">
                            <i class="fas fa-eye mr-1"></i>Detail
                        </a>
                        <a href="{{ route('process.index') }}?search={{ $order->order_number }}"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white text-center py-2 px-3 rounded text-sm font-medium transition duration-200">
                            <i class="fas fa-sync-alt mr-1"></i>Proses
                        </a>
                        <button
                            onclick="confirm('Hapus order {{ $order->order_number }}?') || event.stopImmediatePropagation()"
                            wire:click="deleteOrder({{ $order->id }})"
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white text-center py-2 px-3 rounded text-sm font-medium transition duration-200">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </div>

                    <!-- Estimated Completion -->
                    @if ($order->estimated_completion)
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Perkiraan Selesai:</span>
                                <span
                                    class="font-medium {{ $order->estimated_completion->isPast() && $order->status !== 'completed' ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $order->estimated_completion->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="mobile-card text-center py-8">
                    <i class="fas fa-inbox text-4xl text-gray-400 mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">Tidak ada order</h3>
                    <p class="text-gray-500 mt-1">
                        @if ($search || $statusFilter || $dateFilter !== 'all')
                            Tidak ditemukan order dengan filter yang dipilih.
                        @else
                            Belum ada order yang dibuat.
                        @endif
                    </p>
                    <a href="{{ route('orders.create') }}"
                        class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                        + Buat Order Pertama
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($orders->hasPages())
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div
                class="fixed top-20 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div
                class="fixed top-20 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>

    <style>
        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</div>
