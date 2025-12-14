<div>
    <!-- Page Title -->
    <div class="flex items-center gap-3 mb-6">
        <i class="fas fa-plus-circle text-blue-500 text-xl"></i>
        <h1 class="text-2xl font-bold text-blue-700">Transaksi Baru</h1>
    </div>

    <!-- Progress Steps -->
    <div class="mobile-card">
        <div class="flex justify-between items-center mb-4">
            @foreach (['Pelanggan', 'Layanan', 'Review', 'Bayar'] as $index => $stepName)
                <div class="flex flex-col items-center flex-1">
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
                        {{ $step > $index + 1
                            ? 'bg-green-500 text-white'
                            : ($step == $index + 1
                                ? 'bg-blue-500 text-white'
                                : 'bg-gray-200 text-gray-500') }}">
                        @if ($step > $index + 1)
                            <i class="fas fa-check text-xs"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <span class="text-xs mt-1 {{ $step == $index + 1 ? 'text-blue-600 font-medium' : 'text-gray-500' }}">
                        {{ $stepName }}
                    </span>
                </div>
                @if ($index < 3)
                    <div class="flex-1 h-1 mx-2 {{ $step > $index + 1 ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Step 1: Customer Selection -->
    @if ($step === 1)
        <div class="mobile-card" wire:key="step-1">
            <h2 class="text-lg font-semibold text-blue-700 mb-4">Pilih Pelanggan</h2>

            <!-- Customer Search -->
            <div class="relative mb-4">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" wire:model.live="customerSearch" placeholder="Cari pelanggan..."
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Selected Customer -->
            @if ($this->selectedCustomer)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-green-800">{{ $selectedCustomer->name }}</h3>
                            <p class="text-sm text-green-600">
                                {{ $selectedCustomer->phone ?? 'No telepon tidak tersedia' }}</p>
                            @if ($selectedCustomer->address)
                                <p class="text-sm text-green-600">{{ Str::limit($selectedCustomer->address, 50) }}</p>
                            @endif
                            <p class="text-xs text-green-700 mt-1">
                                <i class="fas fa-user-tag mr-1"></i>
                                {{ $this->selectedCustomer->getMemberStatus() }}
                            </p>
                        </div>
                        <button wire:click="clearCustomer" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Customer List -->
            @if ($customerSearch && !$selectedCustomer)
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                    @forelse($customers as $customer)
                        <div wire:click="selectCustomer({{ $customer->id }})"
                            class="p-3 border-b border-gray-200 hover:bg-blue-50 cursor-pointer transition duration-150">
                            <h4 class="font-medium text-gray-900">{{ $customer->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $customer->phone ?? 'No telepon tidak tersedia' }}</p>
                            <p class="text-xs text-gray-500">{{ $customer->getMemberStatus() }}</p>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500">
                            <i class="fas fa-users text-2xl mb-2"></i>
                            <p>Pelanggan tidak ditemukan</p>
                        </div>
                    @endforelse
                </div>
            @endif

            <!-- New Customer Form -->
            @if (!$selectedCustomer)
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h3 class="text-md font-semibold text-gray-700 mb-3">Pelanggan Baru</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                            <input type="text" wire:model="newCustomer.name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('newCustomer.name') border-red-500 @enderror"
                                placeholder="Masukkan nama pelanggan">
                            @error('newCustomer.name')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Customer</label>
                            <select wire:model="newCustomer.type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="regular">Regular</option>
                                <option value="member">Member</option>
                            </select>
                        </div>

                        @if ($newCustomer['type'] === 'member')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Deposit Awal</label>
                                <input type="number" wire:model="newCustomer.deposit" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan deposit awal">
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                            <input type="tel" wire:model="newCustomer.phone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Contoh: 08123456789">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea wire:model="newCustomer.address" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan alamat pelanggan"></textarea>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation -->
            <div class="flex gap-3 mt-6">
                <a href="{{ route('dashboard') }}"
                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 text-center">
                    Batal
                </a>
                <button wire:click="nextStep"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                    Lanjut ke Layanan <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Step 2: Service Selection - UPDATED -->
    @if ($step === 2)
        <div class="mobile-card" wire:key="step-2">
            <h2 class="text-lg font-semibold text-blue-700 mb-4">Pilih Layanan</h2>
            <p class="text-gray-600 mb-4">Pelanggan: <span
                    class="font-semibold">{{ $this->getSelectedCustomerName() }}</span></p>

            @if ($selectedCustomer && $selectedCustomer->isMember())
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-crown text-green-600 mr-2"></i>
                        <span class="text-green-800 font-medium">Member Benefit Active!</span>
                    </div>
                    <p class="text-sm text-green-700 mt-1">Customer mendapatkan harga khusus member</p>
                </div>
            @endif

            <!-- Services by Category -->
            @forelse($services as $category => $categoryServices)
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-3">{{ $category }}</h3>
                    <div class="space-y-3">
                        @foreach ($categoryServices as $service)
                            <div
                                class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition duration-150
                                {{ in_array($service->id, $selectedServices) ? 'border-blue-500 bg-blue-50' : '' }}">

                                <!-- Service Header -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $service->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $service->duration }}</p>
                                        <p class="text-xs text-gray-500">{{ $service->description }}</p>

                                        <!-- Price Display -->
                                        <div class="mt-2">
                                            @if ($selectedCustomer && $selectedCustomer->isMember() && $service->member_price)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-lg font-bold text-green-600">
                                                        Rp
                                                        {{ number_format($service->member_price, 0, ',', '.') }}/{{ $this->getServiceUnit($service) }}
                                                    </span>
                                                    <span class="text-sm text-gray-500 line-through">
                                                        Rp {{ number_format($service->price, 0, ',', '.') }}
                                                    </span>
                                                    <span
                                                        class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                                        Member
                                                    </span>
                                                </div>
                                            @else
                                                <div class="text-lg font-bold text-green-600">
                                                    Rp
                                                    {{ number_format($service->price, 0, ',', '.') }}/{{ $this->getServiceUnit($service) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <button wire:click="toggleService({{ $service->id }})"
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2 text-sm rounded transition duration-200
                                            {{ in_array($service->id, $selectedServices)
                                                ? 'bg-red-500 hover:bg-red-600 text-white'
                                                : 'bg-blue-500 hover:bg-blue-600 text-white' }}">
                                        {{ in_array($service->id, $selectedServices) ? 'Hapus' : 'Pilih' }}
                                    </button>
                                </div>

                                <!-- Weight/Quantity Input untuk selected services -->
                                @if (in_array($service->id, $selectedServices))
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        @if ($service->is_weight_based)
                                            <!-- Input Berat (Kg) -->
                                            <div class="flex items-center justify-between">
                                                <label class="text-sm font-medium text-gray-700">Berat Pakaian:</label>
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        wire:click="updateWeight({{ $service->id }}, {{ ($serviceWeights[$service->id] ?? 1.0) - 0.5 }})"
                                                        wire:loading.attr="disabled"
                                                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200">
                                                        <i class="fas fa-minus text-gray-600"></i>
                                                    </button>

                                                    <div class="relative">
                                                        <input type="number"
                                                            wire:model="serviceWeights.{{ $service->id }}"
                                                            wire:change="calculateTotal" step="0.1"
                                                            min="0.1" max="50"
                                                            class="w-20 text-center border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <span
                                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">kg</span>
                                                    </div>

                                                    <button
                                                        wire:click="updateWeight({{ $service->id }}, {{ ($serviceWeights[$service->id] ?? 1.0) + 0.5 }})"
                                                        wire:loading.attr="disabled"
                                                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200">
                                                        <i class="fas fa-plus text-gray-600"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <!-- Input Quantity (Baju) -->
                                            <div class="flex items-center justify-between">
                                                <label class="text-sm font-medium text-gray-700">Jumlah Baju:</label>
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        wire:click="updateQuantity({{ $service->id }}, {{ ($serviceQuantities[$service->id] ?? 1) - 1 }})"
                                                        wire:loading.attr="disabled"
                                                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200">
                                                        <i class="fas fa-minus text-gray-600"></i>
                                                    </button>

                                                    <div class="relative">
                                                        <input type="number"
                                                            wire:model="serviceQuantities.{{ $service->id }}"
                                                            wire:change="calculateTotal" min="1"
                                                            max="100"
                                                            class="w-20 text-center border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <span
                                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">pcs</span>
                                                    </div>

                                                    <button
                                                        wire:click="updateQuantity({{ $service->id }}, {{ ($serviceQuantities[$service->id] ?? 1) + 1 }})"
                                                        wire:loading.attr="disabled"
                                                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200">
                                                        <i class="fas fa-plus text-gray-600"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Notes -->
                                        <div class="mt-3">
                                            <input type="text" wire:model="serviceNotes.{{ $service->id }}"
                                                placeholder="Catatan untuk layanan ini..."
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-tags text-3xl mb-2"></i>
                    <p>Tidak ada layanan tersedia</p>
                </div>
            @endforelse

            <!-- Selected Services Summary -->
            @if (count($selectedServices) > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <h4 class="font-semibold text-blue-800 mb-2">Ringkasan Order</h4>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Jumlah Layanan:</span>
                            <span class="font-medium">{{ $this->getServiceCount() }} layanan</span>
                        </div>

                        @if ($totalWeight > 0)
                            <div class="flex justify-between">
                                <span>Total Berat:</span>
                                <span class="font-medium">{{ number_format($totalWeight, 1) }} kg</span>
                            </div>
                        @endif

                        @if ($totalItems > 0)
                            <div class="flex justify-between">
                                <span>Total Item:</span>
                                <span class="font-medium">{{ $totalItems }} pcs</span>
                            </div>
                        @endif

                        <div class="flex justify-between text-lg font-bold border-t border-blue-200 pt-2 mt-2">
                            <span>Total:</span>
                            <span class="text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation -->
            <div class="flex gap-3 mt-6">
                <button wire:click="prevStep" wire:loading.attr="disabled"
                    class="flex-1 bg-gray-500 hover:bg-gray-600 disabled:bg-gray-300 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </button>
                <button wire:click="nextStep" wire:loading.attr="disabled"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 text-white font-medium py-3 px-4 rounded-lg transition duration-200"
                    {{ empty($selectedServices) ? 'disabled' : '' }}>
                    Review Order <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Step 3: Review Order -->
    @if ($step === 3)
        <div class="mobile-card">
            <h2 class="text-lg font-semibold text-blue-700 mb-4">Review Order</h2>

            <!-- Customer Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h3 class="font-semibold text-gray-700 mb-2">Informasi Pelanggan</h3>
                <p class="text-gray-900">{{ $this->getSelectedCustomerName() }}</p>
                @if ($selectedCustomer)
                    <p class="text-sm text-gray-600">{{ $selectedCustomer->phone ?? 'No telepon tidak tersedia' }}</p>
                    @if ($selectedCustomer->address)
                        <p class="text-sm text-gray-600">{{ $selectedCustomer->address }}</p>
                    @endif
                @endif
            </div>

            <!-- Order Items -->
            <div class="mb-4">
                <h3 class="font-semibold text-gray-700 mb-3">Detail Layanan</h3>
                <div class="space-y-3">
                    @foreach ($selectedServices as $serviceId)
                        @php
                            $service = $this->getServiceById($serviceId);
                            $quantity = $serviceQuantities[$serviceId] ?? 1;
                            $notes = $serviceNotes[$serviceId] ?? '';
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $service->name }} -
                                        {{ $service->duration }}</h4>
                                    <p class="text-sm text-gray-600">{{ $service->category }}</p>
                                    @if ($service->size)
                                        <p class="text-xs text-gray-500">Ukuran: {{ $service->size }}</p>
                                    @endif
                                    @if ($notes)
                                        <p class="text-xs text-blue-600 mt-1">Catatan: {{ $notes }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">{{ $quantity }} x Rp
                                        {{ number_format($service->price, 0, ',', '.') }}</div>
                                    <div class="font-semibold text-green-600">Rp
                                        {{ number_format($service->price * $quantity, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Notes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Order</label>
                <textarea wire:model="orderNotes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Tambahkan catatan untuk order ini (opsional)"></textarea>
            </div>

            <!-- Estimated Completion -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Perkiraan Selesai</label>
                <input type="datetime-local" wire:model="estimatedCompletion"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Summary -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-3">Ringkasan Pembayaran</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-lg border-t border-blue-200 pt-2">
                        <span>Total:</span>
                        <span class="text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex gap-3 mt-6">
                <button wire:click="prevStep"
                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </button>
                <button wire:click="nextStep"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                    Pembayaran <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Step 4: Payment -->
    @if ($step === 4)
        <div class="mobile-card">
            <h2 class="text-lg font-semibold text-blue-700 mb-4">Pembayaran</h2>

            <!-- Order Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h3 class="font-semibold text-gray-700 mb-2">Ringkasan Order</h3>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span>Pelanggan:</span>
                        <span class="font-medium">{{ $this->getSelectedCustomerName() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Jumlah Layanan:</span>
                        <span>{{ $this->getServiceCount() }} layanan</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total:</span>
                        <span class="font-semibold text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Metode Pembayaran</label>
                <div class="grid grid-cols-2 gap-3">
                    <label
                        class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition duration-150
                    {{ $paymentMethod === 'cash' ? 'border-blue-500 bg-blue-50' : '' }}">
                        <input type="radio" wire:model="paymentMethod" value="cash" class="hidden">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center
                            {{ $paymentMethod === 'cash' ? 'border-blue-500 bg-blue-500' : '' }}">
                                @if ($paymentMethod === 'cash')
                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium">Tunai</div>
                                <div class="text-xs text-gray-500">Bayar sekarang</div>
                            </div>
                        </div>
                    </label>
                    <label
                        class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition duration-150
                    {{ $paymentMethod === 'transfer' ? 'border-blue-500 bg-blue-50' : '' }}">
                        <input type="radio" wire:model="paymentMethod" value="transfer" class="hidden">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center
                            {{ $paymentMethod === 'transfer' ? 'border-blue-500 bg-blue-500' : '' }}">
                                @if ($paymentMethod === 'transfer')
                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium">Transfer</div>
                                <div class="text-xs text-gray-500">Bayar nanti</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Final Notes -->
            @if ($paymentMethod === 'transfer')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
                        <div class="text-sm text-yellow-700">
                            <strong>Pembayaran Transfer:</strong> Order akan diproses setelah pembayaran dikonfirmasi.
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation -->
            <div class="flex gap-3 mt-6">
                <button wire:click="prevStep"
                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </button>
                <button wire:click="createOrder"
                    class="flex-1 bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-check mr-2"></i>
                    Buat Order
                </button>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-20 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-20 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in">
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
    </style>
</div>
