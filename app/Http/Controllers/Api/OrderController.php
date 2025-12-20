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
        $validator = Validator::make($request->all(), [
            'customer_id'          => 'nullable|exists:customers,id',
            'customer'             => 'nullable|array',
            'customer.name'        => 'required_if:customer_id,null|min:2',
            'customer.type'        => 'required_if:customer_id,null|in:regular,member',
            'items'                => 'required|array|min:1',
            'items.*.service_id'   => 'required|exists:services,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.weight'       => 'nullable|numeric|min:0.1',
            'items.*.notes'        => 'nullable|string',
            'order_notes'          => 'nullable|string',
            'payment_method'       => 'required|in:cash,transfer',
            'estimated_completion' => 'nullable|date',
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
                    $subtotal = $price * $weight;
                    $totalWeight += $weight;
                } else {
                    $subtotal = $price * $item['quantity'];
                }

                $totalAmount += $subtotal;
                $totalItems += $item['quantity'];
            }

            // Handle payment status
            $paymentStatus = $request->payment_method === 'cash' ? 'paid' : 'pending';

            // Create order
            $order = Order::create([
                'customer_id'          => $customer->id,
                'order_number'         => 'HL-' . date('Ymd') . '-' . rand(1000, 9999),
                'order_date'           => now(),
                'status'               => 'request',
                'payment_status'       => $paymentStatus,
                'payment_method'       => $request->payment_method,
                'total_amount'         => $totalAmount,
                'weight'               => $totalWeight > 0 ? $totalWeight : null,
                'total_items'          => $totalItems,
                'notes'                => $request->order_notes,
                'estimated_completion' => $request->estimated_completion,
            ]);

            // Create order items
            foreach ($request->items as $item) {
                $service = Service::find($item['service_id']);
                $price   = $isMember && $service->member_price ? $service->member_price : $service->price;

                if ($service->is_weight_based) {
                    $weight   = $item['weight'] ?? 1.0;
                    $subtotal = $price * $weight;
                } else {
                    $subtotal = $price * $item['quantity'];
                }

                OrderItem::create([
                    'order_id'   => $order->id,
                    'service_id' => $item['service_id'],
                    'quantity'   => $item['quantity'],
                    'weight'     => $service->is_weight_based ? $item['weight'] ?? 1.0 : null,
                    'unit_price' => $price,
                    'subtotal'   => $subtotal,
                    'notes'      => $item['notes'] ?? '',
                ]);
            }

            DB::commit();

            // Send WhatsApp notification
            try {
                $whatsappService = new WhatsAppService();
                $whatsappService->sendInvoice($order);
            } catch (\Exception $e) {
                Log::error('WhatsApp error: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'data'    => $order->load(['customer', 'orderItems.service']),
                'message' => 'Order berhasil dibuat!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create order error: ' . $e->getMessage());

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $order = Order::findOrFail($id);
            $order->update(['payment_status' => $request->payment_status]);

            return response()->json([
                'success' => true,
                'data'    => $order,
                'message' => 'Status pembayaran berhasil diperbarui',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Delete order
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dihapus',
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
            $search = strtolower($request->search); // Konversi ke lowercase
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
