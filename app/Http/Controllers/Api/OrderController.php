<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // Get all orders with filters
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'orderItems.service'])
            ->latest();

        // Search filter
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->date_filter) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', Carbon::yesterday());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', Carbon::now()->month);
                    break;
            }
        }

        // Pagination
        $perPage = $request->per_page ?? 15;
        $orders  = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $orders->items(),
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    // Get order statistics
    public function stats(Request $request)
    {
        $query = Order::query();

        // Date filter
        if ($request->date_filter) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', Carbon::yesterday());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', Carbon::now()->month);
                    break;
            }
        }

        $total     = $query->count();
        $revenue   = $query->sum('total_amount');
        $completed = $query->where('status', 'completed')->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'total'     => $total,
                'revenue'   => $revenue,
                'completed' => $completed,
            ],
        ]);
    }

    // Get single order
    public function show($id)
    {
        $order = Order::with(['customer', 'orderItems.service'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    // Create new order
    public function store(Request $request)
    {
        // Handle FormData
        if ($request->has('data')) {
            $jsonData = json_decode($request->input('data'), true);
            if ($jsonData) {
                $request->merge($jsonData);
            }
        }

        $validator = Validator::make($request->all(), [
            'customer_id'                     => 'nullable|exists:customers,id',
            'customer'                        => 'nullable|array',
            'customer.name'                   => 'required_if:customer_id,null|min:2',
            'customer.type'                   => 'required_if:customer_id,null|in:regular,member',
            'items'                           => 'required|array|min:1',
            'items.*.service_id'              => 'required|exists:services,id',
            'items.*.quantity'                => 'required|integer|min:1',
            'items.*.weight'                  => 'nullable|numeric|min:0.1',
            'items.*.notes'                   => 'nullable|string',
            'items.*.custom_items'            => 'nullable|array',
            'items.*.custom_items.*.id'       => 'required|string',
            'items.*.custom_items.*.name'     => 'required|string',
            'items.*.custom_items.*.quantity' => 'required|integer|min:1',
            'order_notes'                     => 'nullable|string',
            'payment_method'                  => 'required|in:cash,transfer,deposit',
            'payment_confirmation'            => 'required|in:now,later',
            'payment_proof'                   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'estimated_completion'            => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Handle customer
            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
            } else {
                $customer = Customer::create([
                    'name'          => $request->customer['name'],
                    'phone'         => $request->customer['phone'] ?? null,
                    'address'       => $request->customer['address'] ?? null,
                    'type'          => $request->customer['type'],
                    'balance'       => $request->customer['deposit'] ?? 0,
                    'member_since'  => $request->customer['type'] === 'member' ? now() : null,
                    'member_expiry' => $request->customer['type'] === 'member' ? now()->addYear() : null,
                ]);
            }

            // Calculate totals
            $totalAmount = 0;
            $totalWeight = 0;
            $totalItems  = 0;
            $isMember    = $customer->type === 'member';

            foreach ($request->items as $item) {
                $service = Service::find($item['service_id']);
                $price   = $isMember && $service->member_price ? $service->member_price : $service->price;

                if ($service->is_weight_based) {
                    $weight   = $item['weight'] ?? 1.0;
                    $quantity = 1;
                    $subtotal = round($price * $weight, 2);
                    $totalWeight += $weight;
                } else {
                    $quantity = $item['quantity'];
                    $weight   = null;
                    $subtotal = round($price * $quantity, 2);
                }

                $totalAmount += $subtotal;

                if (isset($item['custom_items']) && is_array($item['custom_items'])) {
                    foreach ($item['custom_items'] as $customItem) {
                        $totalItems += $customItem['quantity'] ?? 0;
                    }
                }
            }

            // ✅ LOGIC DEPOSIT MEMBER - Potong saldo jika bayar pakai deposit
            $depositUsed = 0;
            if ($request->payment_method === 'deposit') {
                if ($customer->type !== 'member') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Pembayaran deposit hanya untuk member',
                    ], 400);
                }

                if ($customer->balance < $totalAmount) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Saldo deposit tidak mencukupi. Saldo: ' . number_format($customer->balance, 0, ',', '.'),
                    ], 400);
                }

                // Potong saldo member
                $customer->balance -= $totalAmount;
                $customer->save();
                $depositUsed = $totalAmount;
            }

            // Payment status
            $paymentStatus = $request->payment_confirmation === 'now' ? 'paid' : 'pending';

            // Khusus deposit, payment status selalu paid

            if ($request->payment_method === 'deposit') {
                $paymentStatus = 'paid';
                // Override payment_confirmation untuk deposit
                $request->merge(['payment_confirmation' => 'now']);
            }

            // Handle payment proof upload
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof') && $request->payment_confirmation === 'now') {
                $file             = $request->file('payment_proof');
                $fileName         = 'payment_proof_' . time() . '_' . $customer->id . '.' . $file->getClientOriginalExtension();
                $paymentProofPath = $file->storeAs('payment_proofs', $fileName, 'public');
            }

            // Create order
            $order = Order::create([
                'customer_id'          => $customer->id,
                'order_number'         => 'HL-' . date('Ymd') . '-' . rand(1000, 9999),
                'order_date'           => now(),
                'status'               => 'request',
                'payment_status'       => $paymentStatus,
                'payment_method'       => $request->payment_method,
                'payment_proof'        => $paymentProofPath,
                'total_amount'         => $totalAmount,
                'weight'               => $totalWeight > 0 ? $totalWeight : null,
                'total_items'          => $totalItems,
                'notes'                => $request->order_notes,
                'estimated_completion' => $request->estimated_completion,
                'payment_confirmation' => $request->payment_confirmation,
                'deposit_used'         => $depositUsed, // Tambahkan kolom ini di migration jika belum ada
            ]);

            // Create order items
            foreach ($request->items as $item) {
                $service = Service::find($item['service_id']);
                $price   = $isMember && $service->member_price ? $service->member_price : $service->price;

                if ($service->is_weight_based) {
                    $weight   = $item['weight'] ?? 1.0;
                    $quantity = 1;
                    $subtotal = round($price * $weight, 2);
                } else {
                    $weight   = null;
                    $quantity = $item['quantity'];
                    $subtotal = round($price * $quantity, 2);
                }

                $customItemsJson = isset($item['custom_items']) && is_array($item['custom_items'])
                    ? json_encode($item['custom_items'])
                    : null;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'service_id'   => $item['service_id'],
                    'quantity'     => $quantity,
                    'weight'       => $weight,
                    'unit_price'   => $price,
                    'subtotal'     => $subtotal,
                    'notes'        => $item['notes'] ?? '',
                    'custom_items' => $customItemsJson,
                ]);
            }

            // ✅ TAMBAH DEPOSIT - Hanya jika cash payment dan ada deposit input
            if ($customer->type === 'member'
                && $request->payment_method === 'cash'
                && $request->payment_confirmation === 'now'
                && isset($request->customer['deposit'])
                && $request->customer['deposit'] > 0) {
                $customer->balance += $request->customer['deposit'];
                $customer->save();
            }

            DB::commit();

            Log::info('Order created successfully', [
                'order_id'         => $order->id,
                'order_number'     => $order->order_number,
                'total_amount'     => $totalAmount,
                'payment_method'   => $request->payment_method,
                'deposit_used'     => $depositUsed,
                'customer_balance' => $customer->balance,
            ]);

            $message = 'Order berhasil dibuat!';

            if ($depositUsed > 0) {
                $message = '✅ HL-OK! Pembayaran via deposit berhasil diproses. Saldo terpotong: Rp ' .
                number_format($depositUsed, 0, ',', '.') .
                    '. Status: LUNAS';
            } elseif ($request->payment_confirmation === 'now') {
                $message .= ' Pembayaran telah dikonfirmasi.';
            } else {
                $message .= ' Menunggu konfirmasi pembayaran.';
            }

            return response()->json([
                'success' => true,
                'data'    => $order->load(['customer', 'orderItems.service']),
                'message' => 'Order berhasil dibuat!' .
                ($depositUsed > 0 ? ' Saldo deposit terpotong: Rp ' . number_format($depositUsed, 0, ',', '.') : ''),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create order error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Update order status
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:request,washing,drying,ironing,ready,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $order = Order::findOrFail($id);

            // Validate status progression
            $statusOrder  = ['request', 'washing', 'drying', 'ironing', 'ready', 'completed'];
            $currentIndex = array_search($order->status, $statusOrder);
            $newIndex     = array_search($request->status, $statusOrder);

            if ($newIndex > $currentIndex + 1) {
                $nextStep = $statusOrder[$currentIndex + 1];
                return response()->json([
                    'success' => false,
                    'message' => "Tidak bisa melompat ke step ini. Harus melalui {$nextStep} terlebih dahulu.",
                ], 400);
            }

            $order->update(['status' => $request->status]);

            // Send notification if status is ready
            if ($request->status === 'ready' && ! $order->ready_notification_sent) {
                try {
                    $whatsappService = new WhatsAppService();
                    $whatsappService->sendReadyNotification($order);
                    $order->update(['ready_notification_sent' => true]);
                } catch (\Exception $e) {
                    Log::error('WhatsApp notification error: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data'    => $order->load(['customer', 'orderItems.service']),
                'message' => 'Status order berhasil diperbarui',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Update payment status
    public function updatePaymentStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:paid,pending',
            'payment_proof'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $order = Order::with('customer')->findOrFail($id);

                                                       // Handle payment proof upload
            $paymentProofPath = $order->payment_proof; // Keep existing if not uploading new

            if ($request->hasFile('payment_proof')) {
                // Delete old payment proof if exists
                if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
                    Storage::disk('public')->delete($order->payment_proof);
                }

                // Upload new payment proof
                $file             = $request->file('payment_proof');
                $fileName         = 'payment_proof_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $paymentProofPath = $file->storeAs('payment_proofs', $fileName, 'public');

                Log::info('Payment proof uploaded', [
                    'order_id'  => $order->id,
                    'file_name' => $fileName,
                    'file_path' => $paymentProofPath,
                ]);
            }

            // Update order
            $order->update([
                'payment_status' => $request->payment_status,
                'payment_proof'  => $paymentProofPath,
                'paid_at'        => $request->payment_status === 'paid' ? now() : null,
            ]);

            Log::info('Payment status updated', [
                'order_id'       => $order->id,
                'payment_status' => $request->payment_status,
                'has_proof'      => ! empty($paymentProofPath),
            ]);

            return response()->json([
                'success' => true,
                'data'    => $order->load(['customer', 'orderItems.service']),
                'message' => 'Status pembayaran berhasil diperbarui' .
                ($paymentProofPath ? ' dengan bukti pembayaran' : ''),
            ]);

        } catch (\Exception $e) {
            Log::error('Update payment status error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

// Get payment proof URL
    public function getPaymentProof($id)
    {
        try {
            $order = Order::findOrFail($id);

            if (! $order->payment_proof) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bukti pembayaran tidak tersedia',
                ], 404);
            }

            // Generate URL untuk akses file
            $url = Storage::disk('public')->url($order->payment_proof);

            return response()->json([
                'success' => true,
                'data'    => [
                    'url'       => $url,
                    'file_path' => $order->payment_proof,
                    'file_name' => basename($order->payment_proof),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil bukti pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

// Download payment proof
    public function downloadPaymentProof($id)
    {
        try {
            $order = Order::findOrFail($id);

            if (! $order->payment_proof || ! Storage::disk('public')->exists($order->payment_proof)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bukti pembayaran tidak ditemukan',
                ], 404);
            }

            return Storage::disk('public')->download(
                $order->payment_proof,
                'payment_proof_' . $order->order_number . '.' . pathinfo($order->payment_proof, PATHINFO_EXTENSION)
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh bukti pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Delete order
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);

            // ✅ Kembalikan deposit jika order dibatalkan dan menggunakan deposit
            if ($order->payment_method === 'deposit' && $order->deposit_used > 0) {
                $customer = $order->customer;
                $customer->balance += $order->deposit_used;
                $customer->save();
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dihapus' .
                ($order->deposit_used > 0 ? ' dan deposit dikembalikan' : ''),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus order: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Search customers
    public function searchCustomers(Request $request)
    {
        $query = Customer::query();

        if ($request->search) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"]);
            });
        }

        $customers = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'data'    => $customers,
        ]);
    }

    // Get active services
    public function getServices()
    {
        $services = Service::where('is_active', true)
            ->orderBy('is_weight_based', 'desc')
            ->orderBy('category')
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $services,
        ]);
    }
}
