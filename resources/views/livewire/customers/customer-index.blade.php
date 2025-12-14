<div wire:poll.1s>
    <!-- Page Title dengan Live Indicator -->
    <div class="flex items-center gap-3 mb-6">
        <i class="fas fa-users text-blue-500 text-xl"></i>
        <h1 class="text-2xl font-bold text-blue-700">Manajemen Member</h1>
        <div class="text-xs text-green-500 bg-green-100 px-2 py-1 rounded">
            <i class="fas fa-circle animate-pulse mr-1"></i>Live 1s
        </div>
    </div>

    <!-- Search Bar -->
    <div class="mobile-card">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari member..."
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
    </div>

    <!-- Add Customer Button -->
    <button wire:click="create" wire:loading.attr="disabled"
        class="w-full bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 text-white font-medium py-3 px-4 rounded-lg mb-4 transition duration-200 flex items-center justify-center">
        <i class="fas fa-user-plus mr-2"></i>
        <span wire:loading.remove>Tambah Member Baru</span>
        <span wire:loading>Loading...</span>
    </button>

    <!-- Customer List -->
    <div class="space-y-4">
        @forelse($customers as $customer)
            <div class="mobile-card hover:shadow-md transition duration-200">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="font-semibold text-gray-900">{{ $customer->name }}</h3>
                            <span class="px-2 py-1 text-xs rounded-full {{ $this->getMemberBadge($customer) }}">
                                {{ $this->getMemberStatus($customer) }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-600 mt-1">
                            <i class="fas fa-phone mr-1"></i>{{ $customer->phone ?? '-' }}
                        </p>

                        @if ($customer->address)
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($customer->address, 50) }}
                            </p>
                        @endif

                        @if ($customer->type === 'member' && $customer->balance > 0)
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-wallet mr-1"></i>Balance: Rp
                                {{ number_format($customer->balance, 0, ',', '.') }}
                            </p>
                        @endif

                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-clock mr-1"></i>Bergabung: {{ $customer->created_at->format('d/m/Y') }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <button wire:click="edit({{ $customer->id }})" wire:loading.attr="disabled"
                            class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition duration-200"
                            title="Edit customer">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button wire:click="delete({{ $customer->id }})" wire:loading.attr="disabled"
                            onclick="return confirm('Hapus customer {{ $customer->name }}?')"
                            class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200"
                            title="Hapus customer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="mobile-card text-center py-8">
                <i class="fas fa-users text-4xl text-gray-400 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900">Tidak ada customer</h3>
                <p class="text-gray-500 mt-1">
                    @if ($search)
                        Tidak ditemukan customer dengan kata kunci "{{ $search }}"
                    @else
                        Mulai dengan menambahkan customer pertama Anda.
                    @endif
                </p>
                @if (!$search)
                    <button wire:click="create"
                        class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                        + Tambah Customer Pertama
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($customers->hasPages())
        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if ($showForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            x-data="{ open: true }" x-show="open" @keydown.escape.window="open = false">
            <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-blue-700">
                        {{ $isEditing ? 'Edit Customer' : 'Tambah Customer Baru' }}
                    </h2>
                    <button @click="open = false" wire:click="cancel" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $isEditing ? 'update' : 'store' }}" class="p-4">
                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                            <input type="text" wire:model="name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                                placeholder="Masukkan nama lengkap">
                            @error('name')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Customer</label>
                            <select wire:model="type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="regular">Regular</option>
                                <option value="member">Member</option>
                            </select>
                        </div>

                        <!-- Deposit (hanya untuk member) -->
                        @if ($type === 'member')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Deposit Awal *</label>
                                <input type="number" wire:model="deposit" min="0" step="1000"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('deposit') border-red-500 @enderror"
                                    placeholder="Masukkan deposit awal">
                                @error('deposit')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Deposit akan menjadi saldo awal customer</p>
                            </div>
                        @endif

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                            <input type="tel" wire:model="phone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Contoh: 08123456789">
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea wire:model="address" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i>
                            <span wire:loading.remove>{{ $isEditing ? 'Update' : 'Simpan' }}</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="cancel"
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-20 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-20 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif
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

        .animate-pulse {
            animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            console.log('CustomerIndex component loaded');

            // Auto-close modal ketika berhasil submit
            Livewire.on('customer-updated', () => {
                console.log('Customer updated, modal should close');
            });
        });
    </script>
</div>
