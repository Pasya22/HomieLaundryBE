<!-- resources/views/livewire/orders/show-order.blade.php -->
<div>
    <!-- Header with Back Button -->
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('orders.index') }}" class="text-blue-500 hover:text-blue-700">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-blue-700">Detail Order</h1>
            <p class="text-gray-600">{{ $order->order_number }}</p>
        </div>
    </div>

    <!-- Order Status & Actions -->
    <div class="mobile-card">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-semibold text-blue-700">Status Order</h2>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span
                        class="px-3 py-1 rounded-full text-sm font-medium {{ $this->getStatusBadge($order->status) }}">
                        {{ $statusSteps[$order->status]['label'] }}
                    </span>
                    <span
                        class="px-3 py-1 rounded-full text-sm font-medium {{ $this->getPaymentBadge($order->payment_status) }}">
                        {{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Dibuat</p>
                <p class="text-sm font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="mb-6 overflow-x-auto pb-2">
            <div class="flex items-center relative min-w-max px-2">
                @foreach ($statusSteps as $status => $step)
                    @php
                        $statusIndex = array_search($status, array_keys($statusSteps));
                        $isCompleted = $statusIndex <= $currentStatusIndex;
                        $isCurrent = $status === $order->status;
                    @endphp
                    <div class="flex flex-col items-center mt-2 z-10 min-w-[60px]">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-medium transition-all duration-300
        {{ $isCompleted ? 'bg-green-500' : 'bg-gray-300' }}
        {{ $isCurrent ? 'ring-2 ring-green-400 ring-offset-2 scale-110' : '' }}">
                            @if ($isCompleted)
                                <i class="{{ $step['icon'] }} text-xs"></i>
                            @else
                                {{ $statusIndex + 1 }}
                            @endif
                        </div>
                        <span
                            class="text-xs mt-1 text-center {{ $isCompleted ? 'text-green-600 font-medium' : 'text-gray-500' }}">
                            {{ $step['label'] }}
                        </span>
                    </div>
                    @if ($statusIndex < count($statusSteps) - 1)
                        <div
                            class="h-1 mx-2 -mt-5 min-w-[30px] transition-all duration-300
        {{ $statusIndex < $currentStatusIndex ? 'bg-green-500' : 'bg-gray-200' }}">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Quick Actions -->
        @if ($order->status !== 'completed')
            <div class="border-t pt-4">
                <h3 class="text-md font-semibold text-gray-700 mb-3">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-2">
                    @if ($nextStatus = $this->getNextStatus())
                        <button wire:click="updateStatus('{{ $nextStatus }}')" wire:loading.attr="disabled"
                            class="bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                            <i class="fas fa-arrow-right mr-1"></i>
                            <span wire:loading.remove wire:target="updateStatus">Ke {{ $statusSteps[$nextStatus]['label'] }}</span>
                            <span wire:loading wire:target="updateStatus">Loading...</span>
                        </button>
                    @endif

                    @if ($order->payment_status === 'pending')
                        <button wire:click="confirmMarkAsPaid" wire:loading.attr="disabled"
                            class="bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                            <i class="fas fa-check mr-1"></i>
                            Tandai Lunas
                        </button>
                    @endif

                    <a href="{{ route('invoice.print', $order) }}" target="_blank"
                        class="bg-purple-500 hover:bg-purple-600 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition duration-200">
                        <i class="fas fa-receipt mr-1"></i>Print Nota
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Customer Information -->
    <div class="mobile-card">
        <h2 class="text-lg font-semibold text-blue-700 mb-4">Informasi Pelanggan</h2>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Nama:</span>
                <span class="font-medium">{{ $order->customer->name }}</span>
            </div>
            @if ($order->customer->phone)
                <div class="flex justify-between">
                    <span class="text-gray-600">Telepon:</span>
                    <a href="tel:{{ $order->customer->phone }}" class="font-medium text-blue-600 hover:text-blue-700">
                        {{ $order->customer->phone }}
                    </a>
                </div>
            @endif
            @if ($order->customer->address)
                <div class="flex flex-col">
                    <span class="text-gray-600 mb-1">Alamat:</span>
                    <span class="font-medium">{{ $order->customer->address }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Order Items -->
    <div class="mobile-card">
        <h2 class="text-lg font-semibold text-blue-700 mb-4">Detail Layanan</h2>
        <div class="space-y-4">
            @foreach ($order->orderItems as $item)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $item->service->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $item->service->category }}</p>
                            @if ($item->service->duration)
                                <p class="text-xs text-gray-500">{{ $item->service->duration }}</p>
                            @endif
                            @if ($item->notes)
                                <p class="text-sm text-blue-600 mt-1">
                                    <i class="fas fa-sticky-note mr-1"></i>{{ $item->notes }}
                                </p>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-600">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Order Summary -->
        <div class="border-t border-gray-200 mt-4 pt-4">
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                    <span>Total:</span>
                    <span class="text-green-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Notes & Info -->
    <div class="mobile-card">
        <h2 class="text-lg font-semibold text-blue-700 mb-4">Informasi Order</h2>
        <div class="space-y-4">
            @if ($order->notes)
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-1">Catatan Order:</h3>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $order->notes }}</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-1">Metode Bayar:</h3>
                    <p class="text-gray-900">{{ $order->payment_method === 'cash' ? 'Tunai' : 'Transfer' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-1">Status Bayar:</h3>
                    <span
                        class="px-2 py-1 rounded-full text-xs font-medium {{ $this->getPaymentBadge($order->payment_status) }}">
                        {{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                    </span>
                </div>
            </div>

            @if ($order->estimated_completion)
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-1">Perkiraan Selesai:</h3>
                    <p class="text-gray-900">{{ $order->estimated_completion->format('d/m/Y H:i') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Status History -->
    <div class="mobile-card">
        <h2 class="text-lg font-semibold text-blue-700 mb-4">Riwayat Status</h2>
        <div class="space-y-4">
            @forelse(array_reverse($statusHistory) as $history)
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                        @if(!$loop->last)
                            <div class="flex-1 w-0.5 bg-gray-200 mt-1 min-h-[40px]"></div>
                        @endif
                    </div>
                    <div class="flex-1 pb-4">
                        <div class="flex justify-between items-start mb-1">
                            <h3 class="font-medium text-gray-900">{{ $history['label'] }}</h3>
                            <span class="text-xs text-gray-500 whitespace-nowrap ml-2">
                                {{ $history['timestamp']->format('d/m H:i') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-1">{{ $history['description'] }}</p>
                        <p class="text-xs text-gray-500">Oleh: {{ $history['user'] }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-gray-500">
                    <i class="fas fa-history text-2xl mb-2"></i>
                    <p>Belum ada riwayat status</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-2 gap-3 mt-6 mb-6">
        <a href="{{ route('orders.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 text-center">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>

        @if ($order->status === 'ready')
            <button wire:click="markAsCompleted" wire:loading.attr="disabled"
                class="bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                <i class="fas fa-flag-checkered mr-2"></i>
                <span wire:loading.remove wire:target="markAsCompleted">Tandai Selesai</span>
                <span wire:loading wire:target="markAsCompleted">Loading...</span>
            </button>
        @elseif($order->status === 'completed')
            <div class="bg-green-100 text-green-700 font-medium py-3 px-4 rounded-lg text-center">
                <i class="fas fa-check-circle mr-2"></i>Order Selesai
            </div>
        @else
            <button
                onclick="alert('Order harus dalam status SIAP DIAMBIL sebelum bisa ditandai selesai.\n\nStatus saat ini: {{ $statusSteps[$order->status]['label'] }}')"
                class="bg-gray-300 cursor-not-allowed text-gray-600 font-medium py-3 px-4 rounded-lg">
                <i class="fas fa-flag-checkered mr-2"></i>Tandai Selesai
            </button>
        @endif
    </div>

    <!-- Confirm Payment Modal -->
    @if ($showConfirmPayment)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            x-data="{ open: @entangle('showConfirmPayment') }"
            x-show="open"
            @click.self="$wire.set('showConfirmPayment', false)"
            @keydown.escape.window="$wire.set('showConfirmPayment', false)">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Konfirmasi Pembayaran</h3>
                <p class="text-gray-600 mb-4">
                    Apakah Anda yakin ingin menandai order ini sebagai LUNAS?
                </p>
                <div class="bg-gray-50 p-3 rounded-lg mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span>No. Order:</span>
                        <span class="font-medium">{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total:</span>
                        <span class="text-green-600">Rp {{ number_format($confirmPaymentAmount, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button wire:click="markAsPaid" wire:loading.attr="disabled"
                        class="flex-1 bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                        <span wire:loading.remove wire:target="markAsPaid">Ya, Tandai Lunas</span>
                        <span wire:loading wire:target="markAsPaid">Processing...</span>
                    </button>
                    <button wire:click="$set('showConfirmPayment', false)"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-20 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in flash-message"
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-20 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in flash-message"
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif
</div>
