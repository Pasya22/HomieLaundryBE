<!-- resources/views/livewire/process/process-index.blade.php -->
<div>
    <!-- Page Title -->
    <div class="flex items-center gap-3 mb-6">
        <i class="fas fa-sync-alt text-blue-500 text-xl"></i>
        <h1 class="text-2xl font-bold text-blue-700">Update Proses Laundry</h1>
    </div>

    <!-- Search & Filter -->
    <div class="mobile-card">
        <!-- Search -->
        <div class="relative mb-4">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="Cari order (No. Order / Nama Pelanggan)..."
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <!-- Status Filter -->
        <div class="flex gap-2 overflow-x-auto pb-1">
            <button wire:click="$set('statusFilter', '')"
                class="flex-shrink-0 px-3 py-2 rounded-full text-sm font-medium {{ !$statusFilter ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                Semua Status
            </button>
            @foreach ($statusOptions as $value => $label)
                <button wire:click="$set('statusFilter', '{{ $value }}')"
                    class="flex-shrink-0 px-3 py-2 rounded-full text-sm font-medium {{ $statusFilter == $value ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Orders List -->
    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="mobile-card">
                <!-- Order Header -->
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $order->order_number }}</h3>
                        <p class="text-sm text-gray-600">{{ $order->customer->name }}</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <span
                            class="inline-block px-3 py-1 rounded-full text-sm font-medium {{ $this->getStatusBadge($order->status) }}">
                            <i class="{{ $this->getStatusIcon($order->status) }} mr-1"></i>
                            {{ $statusOptions[$order->status] }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">
                            Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <!-- Order Items Preview -->
                <div class="mb-3">
                    @foreach ($order->orderItems->take(2) as $item)
                        <div
                            class="flex justify-between text-sm py-1 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                            <span class="text-gray-600">
                                {{ $item->service->name }} ({{ $item->quantity }}x)
                            </span>
                            <span class="font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    @if ($order->orderItems->count() > 2)
                        <div class="text-xs text-gray-500 text-center mt-1">
                            +{{ $order->orderItems->count() - 2 }} layanan lainnya
                        </div>
                    @endif
                </div>

                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>Progress</span>
                        <span>
                            @php
                                $statusOrder = array_keys($statusOptions);
                                $currentIndex = array_search($order->status, $statusOrder);
                                $progress = (($currentIndex + 1) / count($statusOrder)) * 100;
                            @endphp
                            {{ round($progress) }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                            style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex gap-2 flex-wrap">
                    <!-- Quick Status Update -->
                    @if ($nextStatus = $this->getNextStatus($order->status))
                        <button wire:click="quickUpdateStatus({{ $order->id }}, '{{ $nextStatus }}')"
                            wire:loading.attr="disabled"
                            class="bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 text-white px-3 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                            <i class="fas fa-arrow-right mr-1"></i>
                            <span wire:loading.remove
                                wire:target="quickUpdateStatus({{ $order->id }}, '{{ $nextStatus }}')">{{ $statusOptions[$nextStatus] }}</span>
                            <span wire:loading
                                wire:target="quickUpdateStatus({{ $order->id }}, '{{ $nextStatus }}')"><i
                                    class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    @endif

                    <!-- Mark as Ready -->
                    @if ($order->status !== 'ready' && $order->status !== 'completed')
                        <button wire:click="markAsReady({{ $order->id }})" wire:loading.attr="disabled"
                            class="bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white px-3 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                            <i class="fas fa-check-circle mr-1"></i>
                            <span wire:loading.remove wire:target="markAsReady({{ $order->id }})">Siap
                                Diambil</span>
                            <span wire:loading wire:target="markAsReady({{ $order->id }})"><i
                                    class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    @endif

                    <!-- Mark as Completed -->
                    @if ($order->status === 'ready')
                        <button wire:click="markAsCompleted({{ $order->id }})" wire:loading.attr="disabled"
                            class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-3 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                            <i class="fas fa-flag-checkered mr-1"></i>
                            <span wire:loading.remove wire:target="markAsCompleted({{ $order->id }})">Selesai</span>
                            <span wire:loading wire:target="markAsCompleted({{ $order->id }})"><i
                                    class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    @endif

                    <!-- Detailed Update -->
                    <button wire:click="selectOrder({{ $order->id }})" wire:loading.attr="disabled"
                        class="bg-gray-500 hover:bg-gray-600 disabled:bg-gray-300 text-white px-3 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                        <i class="fas fa-edit mr-1"></i>
                        Detail
                    </button>

                    <!-- View Order -->
                    <a href="{{ route('orders.show', $order) }}"
                        class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                        <i class="fas fa-eye mr-1"></i>
                        Lihat
                    </a>
                </div>

                <!-- Estimated Completion -->
                @if ($order->estimated_completion)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Perkiraan Selesai:</span>
                            <span
                                class="font-medium {{ $order->estimated_completion->isPast() ? 'text-red-600' : 'text-green-600' }}">
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
                    @if ($search || $statusFilter)
                        Tidak ditemukan order dengan filter yang dipilih.
                    @else
                        Semua order sudah selesai atau belum ada order hari ini.
                    @endif
                </p>
                @if (!$search && !$statusFilter)
                    <a href="{{ route('orders.create') }}"
                        class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                        + Buat Order Baru
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($orders->hasPages())
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif

    <!-- Update Status Modal -->
    @if ($showUpdateModal && $selectedOrder)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            x-data="{ open: @entangle('showUpdateModal') }" x-show="open" @click.self="$wire.set('showUpdateModal', false)"
            @keydown.escape.window="$wire.set('showUpdateModal', false)">
            <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="p-4 border-b sticky top-0 bg-white z-10">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-semibold text-blue-700">Update Status Order</h2>
                            <p class="text-sm text-gray-600">{{ $selectedOrder->order_number }} -
                                {{ $selectedOrder->customer->name }}</p>
                        </div>
                        <button wire:click="$set('showUpdateModal', false)" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="updateOrderStatus" class="p-4">
                    <div class="space-y-4">
                        <!-- Current Status -->
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Saat Ini</label>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-medium {{ $this->getStatusBadge($selectedOrder->status) }}">
                                    <i class="{{ $this->getStatusIcon($selectedOrder->status) }} mr-1"></i>
                                    {{ $statusOptions[$selectedOrder->status] }}
                                </span>
                            </div>
                        </div>

                        <!-- New Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($statusOptions as $value => $label)
                                    <label
                                        class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition duration-150
                            {{ $newStatus === $value ? 'border-blue-500 bg-blue-50' : '' }}">
                                        <input type="radio" wire:model.live="newStatus" value="{{ $value }}"
                                            class="hidden">
                                        <div class="flex items-center gap-2 w-full">
                                            <div
                                                class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center
                                    {{ $newStatus === $value ? 'border-blue-500 bg-blue-500' : '' }}">
                                                @if ($newStatus === $value)
                                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-sm">{{ $label }}</div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Estimated Completion -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Perkiraan Selesai</label>
                            <input type="datetime-local" wire:model="estimatedCompletion"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Process Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Proses</label>
                            <textarea wire:model="processNotes" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Tambahkan catatan tentang proses laundry (opsional)"></textarea>
                        </div>

                        <!-- Order Items Preview -->
                        <div class="border-t pt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Detail Layanan</h3>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                @foreach ($selectedOrder->orderItems as $item)
                                    <div class="flex justify-between text-sm p-2 bg-gray-50 rounded">
                                        <div>
                                            <span class="font-medium">{{ $item->service->name }}</span>
                                            <span class="text-gray-600">({{ $item->quantity }}x)</span>
                                        </div>
                                        <span class="font-medium">Rp
                                            {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" wire:click="$set('showUpdateModal', false)"
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i>
                            <span wire:loading.remove wire:target="updateOrderStatus">Update Status</span>
                            <span wire:loading wire:target="updateOrderStatus">Updating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-20 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in flash-message"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-20 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in flash-message"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif
</div>
