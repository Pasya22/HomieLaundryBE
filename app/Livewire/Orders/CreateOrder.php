<?php
// app/Livewire/Orders/CreateOrder.php

namespace App\Livewire\Orders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CreateOrder extends Component
{
    public $step = 1;

    // Customer Step
    public $customerSearch   = '';
    public $selectedCustomer = null;
    public $newCustomer      = [
        'name'    => '',
        'phone'   => '',
        'address' => '',
        'type'    => 'regular',
        'deposit' => 0,
    ];

    // Services Step
    public $selectedServices  = [];
    public $serviceQuantities = []; // Jumlah baju (untuk satuan)
    public $serviceWeights    = []; // Berat kg (untuk kiloan)
    public $serviceNotes      = [];

    // Order Details
    public $orderNotes    = '';
    public $paymentMethod = 'cash';
    public $estimatedCompletion;
    public $useDeposit = false;

    // Calculated
    public $subtotal  = 0;
    public $total     = 0;
    public $isLoading = false;

    // Summary
    public $totalWeight = 0;
    public $totalItems  = 0;

    protected $rules = [
        'newCustomer.name'    => 'required_if:selectedCustomer,null|min:2',
        'newCustomer.phone'   => 'nullable|string',
        'newCustomer.address' => 'nullable|string',
    ];

    public function mount()
    {
        $this->estimatedCompletion = now()->addHours(6)->format('Y-m-d\TH:i');
    }

    public function render()
    {
        $customers = [];
        $services  = [];

        if ($this->step === 1) {
            $customers = Customer::when($this->customerSearch, function ($query) {
                return $query->where('name', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
            })->limit(10)->get();
        }

        if ($this->step === 2) {
            $services = Service::where('is_active', true)
                ->orderBy('is_weight_based', 'desc') // Kiloan dulu
                ->orderBy('category')
                ->orderBy('price')
                ->get()
                ->groupBy('category');
        }

        return view('livewire.orders.create-order', compact('customers', 'services'));
    }

    // ==================== CUSTOMER METHODS ====================

    public function selectCustomer($customerId)
    {
        $this->selectedCustomer = Customer::find($customerId);
        $this->calculateTotal(); // Recalculate dengan harga member jika ada
    }

    public function clearCustomer()
    {
        $this->selectedCustomer = null;
        $this->calculateTotal();
    }

    public function validateCustomerStep()
    {
        if ($this->selectedCustomer) {
            return true;
        }

        $validated = $this->validate([
            'newCustomer.name'    => 'required|min:2',
            'newCustomer.phone'   => 'nullable|string',
            'newCustomer.address' => 'nullable|string',
            'newCustomer.type'    => 'required|in:regular,member',
            'newCustomer.deposit' => 'nullable|numeric|min:0',
        ]);

        if ($this->newCustomer['type'] === 'member' && $this->newCustomer['deposit'] <= 0) {
            session()->flash('error', 'Member wajib memiliki deposit awal.');
            return false;
        }

        return true;
    }

    // ==================== SERVICE METHODS ====================

    public function toggleService($serviceId)
    {
        if ($this->isLoading) {
            return;
        }

        $serviceId = (int) $serviceId;
        $service   = Service::find($serviceId);

        if (in_array($serviceId, $this->selectedServices)) {
            // Remove service
            $this->selectedServices = array_diff($this->selectedServices, [$serviceId]);
            unset($this->serviceQuantities[$serviceId]);
            unset($this->serviceWeights[$serviceId]);
            unset($this->serviceNotes[$serviceId]);
        } else {
            // Add service
            $this->selectedServices[] = $serviceId;

            if ($service->is_weight_based) {
                // Layanan kiloan - default 1 kg
                $this->serviceWeights[$serviceId] = 1.0;
                $this->serviceQuantities[$serviceId] = 1; // Tetap track jumlah baju untuk laporan
            } else {
                // Layanan satuan - default 1 piece
                $this->serviceQuantities[$serviceId] = 1;
                $this->serviceWeights[$serviceId] = null; // Tidak perlu weight
            }

            $this->serviceNotes[$serviceId] = '';
        }

        $this->calculateTotal();
    }

    public function updateQuantity($serviceId, $quantity)
    {
        if ($this->isLoading) {
            return;
        }

        $serviceId = (int) $serviceId;
        $quantity  = max(1, min(100, (int) $quantity));

        $this->serviceQuantities[$serviceId] = $quantity;
        $this->calculateTotal();
    }

    public function updateWeight($serviceId, $weight)
    {
        if ($this->isLoading) {
            return;
        }

        $serviceId = (int) $serviceId;
        $weight    = max(0.1, min(50, (float) $weight));

        $this->serviceWeights[$serviceId] = $weight;
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->subtotal = 0;
        $this->totalWeight = 0;
        $this->totalItems = 0;

        $isMember = $this->selectedCustomer && $this->selectedCustomer->isMember();

        foreach ($this->selectedServices as $serviceId) {
            $service = $this->getServiceById($serviceId);

            if (!$service) continue;

            if ($service->is_weight_based) {
                // Layanan KILOAN
                $weight = $this->serviceWeights[$serviceId] ?? 1.0;
                $quantity = $this->serviceQuantities[$serviceId] ?? 0; // Jumlah baju (opsional)

                $unitPrice = $service->getPrice($isMember);
                $itemSubtotal = $unitPrice * $weight;

                $this->totalWeight += $weight;
                $this->totalItems += $quantity; // Track jumlah baju

            } else {
                // Layanan SATUAN
                $quantity = $this->serviceQuantities[$serviceId] ?? 1;

                $unitPrice = $service->getPrice($isMember);
                $itemSubtotal = $unitPrice * $quantity;

                $this->totalItems += $quantity;
            }

            $this->subtotal += $itemSubtotal;
        }

        $this->total = $this->subtotal; // Tambahkan diskon/pajak di sini nanti
    }

    // ==================== STEP NAVIGATION ====================

    public function nextStep()
    {
        if ($this->isLoading) {
            return;
        }

        $this->isLoading = true;

        try {
            if ($this->step === 1) {
                if (!$this->validateCustomerStep()) {
                    $this->isLoading = false;
                    return;
                }
            } elseif ($this->step === 2) {
                if (empty($this->selectedServices)) {
                    session()->flash('error', 'Pilih minimal satu layanan!');
                    $this->isLoading = false;
                    return;
                }
            }

            if ($this->step < 4) {
                $this->step++;
                $this->calculateTotal();
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function prevStep()
    {
        if ($this->isLoading) {
            return;
        }

        if ($this->step > 1) {
            $this->step--;
        }
    }

    // ==================== ORDER CREATION ====================

    public function createOrder()
    {
        if (empty($this->selectedServices)) {
            session()->flash('error', 'Pilih minimal satu layanan!');
            return;
        }

        try {
            DB::beginTransaction();

            // Create customer if new
            if (!$this->selectedCustomer) {
                $this->selectedCustomer = Customer::create([
                    'name'          => $this->newCustomer['name'],
                    'phone'         => $this->newCustomer['phone'] ?? null,
                    'address'       => $this->newCustomer['address'] ?? null,
                    'type'          => $this->newCustomer['type'],
                    'deposit'       => $this->newCustomer['type'] === 'member' ? $this->newCustomer['deposit'] : 0,
                    'balance'       => $this->newCustomer['type'] === 'member' ? $this->newCustomer['deposit'] : 0,
                    'member_since'  => $this->newCustomer['type'] === 'member' ? now() : null,
                    'member_expiry' => $this->newCustomer['type'] === 'member' ? now()->addYear() : null,
                ]);
            }

            // Handle payment
            $paymentStatus = 'pending';
            if ($this->paymentMethod === 'cash') {
                $paymentStatus = 'paid';
            } elseif ($this->useDeposit && $this->selectedCustomer->canUseDeposit($this->total)) {
                $this->selectedCustomer->deductBalance($this->total);
                $paymentStatus = 'paid';
            }

            // Create order
            $order = Order::create([
                'customer_id'          => $this->selectedCustomer->id,
                'order_number'         => 'HL-' . date('Ymd') . '-' . rand(1000, 9999),
                'order_date'           => now(),
                'status'               => 'request',
                'payment_status'       => $paymentStatus,
                'payment_method'       => $this->paymentMethod,
                'total_amount'         => $this->total,
                'weight'               => $this->totalWeight > 0 ? $this->totalWeight : null,
                'total_items'          => $this->totalItems, // Total jumlah baju
                'notes'                => $this->orderNotes,
                'estimated_completion' => $this->estimatedCompletion ? Carbon::parse($this->estimatedCompletion) : null,
            ]);

            // Create order items
            $isMember = $this->selectedCustomer->isMember();

            foreach ($this->selectedServices as $serviceId) {
                $service = Service::find($serviceId);

                if (!$service) continue;

                $unitPrice = $service->getPrice($isMember);
                $quantity = $this->serviceQuantities[$serviceId] ?? 1;
                $weight = $this->serviceWeights[$serviceId] ?? null;

                if ($service->is_weight_based) {
                    // Layanan KILOAN
                    $subtotal = $unitPrice * $weight;
                } else {
                    // Layanan SATUAN
                    $subtotal = $unitPrice * $quantity;
                }

                OrderItem::create([
                    'order_id'   => $order->id,
                    'service_id' => $serviceId,
                    'quantity'   => $quantity, // Jumlah baju
                    'weight'     => $weight, // Berat (untuk kiloan)
                    'unit_price' => $unitPrice,
                    'subtotal'   => $subtotal,
                    'notes'      => $this->serviceNotes[$serviceId] ?? '',
                ]);
            }

            DB::commit();

            // Send WhatsApp
            try {
                $whatsappService = new WhatsAppService();
                $whatsappService->sendInvoice($order);
            } catch (\Exception $e) {
                Log::error('WhatsApp error', ['error' => $e->getMessage()]);
            }

            session()->flash('success', 'Order berhasil dibuat! No. Order: ' . $order->order_number);

            $this->resetForm();
            return redirect()->route('orders.show', $order);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create order error', ['error' => $e->getMessage()]);
            session()->flash('error', 'Gagal membuat order: ' . $e->getMessage());
        }
    }

    // ==================== HELPER METHODS ====================

    private function resetForm()
    {
        $this->reset([
            'step', 'customerSearch', 'selectedCustomer', 'newCustomer',
            'selectedServices', 'serviceQuantities', 'serviceWeights', 'serviceNotes',
            'orderNotes', 'paymentMethod', 'subtotal', 'total', 'useDeposit',
            'totalWeight', 'totalItems',
        ]);

        $this->newCustomer = [
            'name' => '',
            'phone' => '',
            'address' => '',
            'type' => 'regular',
            'deposit' => 0
        ];

        $this->estimatedCompletion = now()->addHours(6)->format('Y-m-d\TH:i');
    }

    public function getSelectedCustomerName()
    {
        if ($this->selectedCustomer) {
            return $this->selectedCustomer->name;
        }
        return $this->newCustomer['name'] ?: 'Pelanggan Baru';
    }

    public function getServiceCount()
    {
        return count($this->selectedServices);
    }

    public function getServiceById($serviceId)
    {
        return Service::find($serviceId);
    }

    public function getServiceType($service)
    {
        return $service->is_weight_based ? 'Per Kg' : 'Per Piece';
    }

    public function getServiceUnit($service)
    {
        return $service->is_weight_based ? 'kg' : 'pcs';
    }
}
