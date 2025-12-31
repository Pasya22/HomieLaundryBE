<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProcessController extends Controller
{
    /**
     * Get orders for process management
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'orderItems.service'])
            ->latest();

        // Status filter - only apply if explicitly provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // If no status filter, show all orders

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $request->per_page ?? 10;
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

    /**
     * Update order status with validation
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status'               => 'required|in:request,washing,drying,ironing,ready,completed',
            'notes'                => 'nullable|string',
            'estimated_completion' => 'nullable|date',
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
                $nextStep     = $statusOrder[$currentIndex + 1];
                $statusLabels = [
                    'request'   => 'Penerimaan',
                    'washing'   => 'Pencucian',
                    'drying'    => 'Pengeringan',
                    'ironing'   => 'Setrika',
                    'ready'     => 'Siap Diambil',
                    'completed' => 'Selesai',
                ];

                return response()->json([
                    'success' => false,
                    'message' => "Tidak bisa melompat ke step ini. Harus melalui {$statusLabels[$nextStep]} terlebih dahulu.",
                ], 400);
            }

            $updateData = ['status' => $request->status];

            if ($request->has('estimated_completion')) {
                $updateData['estimated_completion'] = $request->estimated_completion;
            }

            // Add notes to order notes if provided
            if ($request->filled('notes')) {
                $currentNotes        = $order->notes ? $order->notes . "\n" : '';
                $updateData['notes'] = $currentNotes . '[' . now()->format('d/m/Y H:i') . '] ' . $request->notes;
            }

            $order->update($updateData);

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

    /**
     * Quick update to next status
     */
    public function quickUpdate(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            $statusOrder  = ['request', 'washing', 'drying', 'ironing', 'ready', 'completed'];
            $currentIndex = array_search($order->status, $statusOrder);

            if ($currentIndex === false || $currentIndex >= count($statusOrder) - 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order sudah dalam status akhir',
                ], 400);
            }

            $nextStatus = $statusOrder[$currentIndex + 1];
            $order->update(['status' => $nextStatus]);

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

    /**
     * Mark order as ready
     */
    public function markAsReady($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->update(['status' => 'ready']);

            return response()->json([
                'success' => true,
                'data'    => $order->load(['customer', 'orderItems.service']),
                'message' => 'Order ditandai sebagai SIAP DIAMBIL',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark order as completed
     */
    public function markAsCompleted($id)
    {
        try {
            $order = Order::findOrFail($id);

            if ($order->status !== 'ready') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order harus dalam status SIAP DIAMBIL sebelum bisa diselesaikan',
                ], 400);
            }

            $order->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'data'    => $order->load(['customer', 'orderItems.service']),
                'message' => 'Order ditandai sebagai SELESAI',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
