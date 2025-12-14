<!-- resources/views/livewire/dashboard.blade.php -->
<div>
    <div wire:poll.1s>

        <!-- Page Title -->
        <div class="flex items-center gap-3 mb-6">
            <i class="fas fa-home text-blue-500 text-xl"></i>
            <h1 class="text-2xl font-bold text-blue-700">Dashboard</h1>
        </div>

        <!-- Quick Actions -->
        <div class="mobile-card">
            <h2 class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Aksi Cepat</h2>

            <a href="{{ route('orders.create') }}"
                class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center font-medium py-3 px-4 rounded-lg mb-3 transition duration-200">
                <i class="fas fa-plus mr-2"></i>Transaksi Baru
            </a>

            <a href="{{ route('process.index') }}"
                class="block w-full bg-white border border-blue-500 text-blue-500 hover:bg-blue-50 text-center font-medium py-3 px-4 rounded-lg mb-3 transition duration-200">
                <i class="fas fa-sync-alt mr-2"></i>Update Proses
            </a>

            <div class="flex gap-3">
                <a href="{{ route('customers.index') }}"
                    class="flex-1 bg-white border border-blue-500 text-blue-500 hover:bg-blue-50 text-center font-medium py-2 px-3 rounded-lg text-sm transition duration-200">
                    <i class="fas fa-users mr-1"></i>Member
                </a>
                <a href="{{ route('services.index') }}"
                    class="flex-1 bg-white border border-blue-500 text-blue-500 hover:bg-blue-50 text-center font-medium py-2 px-3 rounded-lg text-sm transition duration-200">
                    <i class="fas fa-tag mr-1"></i>Harga
                </a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="mobile-card">
            <h2 class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Transaksi Terbaru</h2>

            <div class="space-y-3">
                @forelse($recentOrders as $order)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">{{ $order->order_number }}</div>
                            <div class="text-sm text-gray-600">{{ $order->customer->name }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="status-badge {{ $this->getStatusBadge($order->status) }}">
                                {{ $this->getStatusText($order->status) }}
                            </span>
                            <a href="{{ route('orders.show', ['order' => $order->id]) }}"
                                class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p>Belum ada transaksi hari ini</p>
                    </div>
                @endforelse
            </div>

            <a href="{{ route('orders.index') }}"
                class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-center font-medium py-2 px-4 rounded-lg mt-3 transition duration-200">
                <i class="fas fa-list mr-2"></i>Lihat Semua
            </a>
        </div>

        <!-- Stats -->
        <div class="mobile-card">
            <h2 class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Statistik Hari Ini</h2>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-700">{{ $todayOrders }}</div>
                    <div class="text-sm text-blue-600">Total Transaksi</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-700">Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-green-600">Pendapatan</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-purple-700">{{ $completedToday }}</div>
                    <div class="text-sm text-purple-600">Selesai</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-orange-700">{{ $inProgress }}</div>
                    <div class="text-sm text-orange-600">Dalam Proses</div>
                </div>
            </div>
        </div>
    </div>
</div>
