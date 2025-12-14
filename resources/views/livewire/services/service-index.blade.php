<!-- resources/views/livewire/services/service-index.blade.php -->
<div>
    <!-- Page Title dengan Live Indicator -->
    <div class="flex items-center gap-3 mb-6">
        <i class="fas fa-tag text-blue-500 text-xl"></i>
        <h1 class="text-2xl font-bold text-blue-700">Daftar Harga Layanan</h1>
        <div class="text-xs text-green-500 bg-green-100 px-2 py-1 rounded">
            <i class="fas fa-circle animate-pulse mr-1"></i>Live
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="mobile-card">
        <!-- Search -->
        <div class="relative mb-4">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari layanan..."
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <!-- Category Filter -->
        <div class="flex gap-2 overflow-x-auto pb-2">
            <button type="button" wire:click="$set('selectedCategory', '')"
                class="flex-shrink-0 px-3 py-2 rounded-full text-sm font-medium {{ !$selectedCategory ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                Semua ({{ array_sum($categoryCounts->toArray()) }})
            </button>
            @foreach ($categoryCounts as $category => $count)
                <button type="button" wire:click="$set('selectedCategory', '{{ $category }}')"
                    class="flex-shrink-0 px-3 py-2 rounded-full text-sm font-medium {{ $selectedCategory == $category ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                    {{ $category }} ({{ $count }})
                </button>
            @endforeach
        </div>
    </div>

    <!-- Add Service Button -->
    <button type="button" wire:click="create" wire:loading.attr="disabled"
        class="w-full bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 text-white font-medium py-3 px-4 rounded-lg mb-4 transition duration-200 flex items-center justify-center">
        <i class="fas fa-plus-circle mr-2"></i>
        <span wire:loading.remove wire:target="create">Tambah Layanan Baru</span>
        <span wire:loading wire:target="create">Loading...</span>
    </button>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 gap-4">
        @forelse($services as $service)
            <div class="mobile-card">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3 flex-1">
                        <div
                            class="w-12 h-12 {{ $service->getCategoryBadge() }} rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="{{ $service->getIconClass() }} text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $service->name }}</h3>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span
                                    class="inline-block px-2 py-1 text-xs rounded-full {{ $service->getCategoryBadge() }}">
                                    {{ $service->category }}
                                </span>
                                @if ($service->size)
                                    <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full">
                                        {{ $service->size }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $service->duration }}</p>
                            @if ($service->description)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $service->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-3">
                        <div class="text-lg font-bold text-green-600 whitespace-nowrap">
                            Rp {{ number_format($service->price, 0, ',', '.') }}
                        </div>
                        <div class="flex gap-1 mt-2 justify-end">
                            <button type="button" wire:click="edit({{ $service->id }})" wire:loading.attr="disabled"
                                class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition duration-200"
                                title="Edit layanan">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button"
                                onclick="if(confirm('Hapus layanan {{ $service->name }}?')) { @this.call('delete', {{ $service->id }}) }"
                                class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200"
                                title="Hapus layanan">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="mobile-card text-center py-8">
                <i class="fas fa-tags text-4xl text-gray-400 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900">Tidak ada layanan</h3>
                <p class="text-gray-500 mt-1">
                    @if ($search || $selectedCategory)
                        Tidak ditemukan layanan dengan filter yang dipilih.
                    @else
                        Mulai dengan menambahkan layanan pertama Anda.
                    @endif
                </p>
                @if (!$search && !$selectedCategory)
                    <button type="button" wire:click="create"
                        class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                        + Tambah Layanan Pertama
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($services->hasPages())
        <div class="mt-4">
            {{ $services->links() }}
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if ($showForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            x-data="{ open: @entangle('showForm') }"
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            @click.self="$wire.cancel()"
            @keydown.escape.window="$wire.cancel()">
            <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="p-4 border-b sticky top-0 bg-white z-10">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-blue-700">
                            {{ $isEditing ? 'Edit Layanan' : 'Tambah Layanan Baru' }}
                        </h2>
                        <button wire:click="cancel" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="{{ $isEditing ? 'update' : 'store' }}" class="p-4">
                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Layanan *</label>
                            <input type="text" wire:model="name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                                placeholder="Contoh: REGULER, EXPRESS">
                            @error('name')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                            <input type="text" wire:model="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror"
                                placeholder="Contoh: PAKAIAN, SELIMUT, JAS">
                            @error('category')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Size -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran</label>
                            <select wire:model="size"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($this->getSizes() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Duration -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durasi *</label>
                            <select wire:model="duration"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('duration') border-red-500 @enderror">
                                <option value="">Pilih Durasi</option>
                                @foreach ($this->getDurations() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('duration')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label>
                            <input type="number" wire:model="price" min="0" step="1000"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('price') border-red-500 @enderror"
                                placeholder="Contoh: 15000">
                            @error('price')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Icon -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                            <select wire:model="icon"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Icon</option>
                                @foreach ($this->getIcons() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea wire:model="description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Deskripsi layanan (opsional)"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i>
                            <span wire:loading.remove wire:target="{{ $isEditing ? 'update' : 'store' }}">{{ $isEditing ? 'Update' : 'Simpan' }}</span>
                            <span wire:loading wire:target="{{ $isEditing ? 'update' : 'store' }}">Menyimpan...</span>
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
            x-init="setTimeout(() => show = false, 4000)">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif
</div>
