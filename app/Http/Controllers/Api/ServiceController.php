<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Get all services grouped by category
     */
    public function index(Request $request)
    {
        $query = Service::query();

        // Filter hanya yang aktif jika diminta
        if ($request->has('active_only') && $request->active_only) {
            $query->where('is_active', true);
        }

        $services = $query->orderBy('category')
            ->orderBy('price')
            ->get()
            ->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Create service
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'member_price' => 'nullable|numeric|min:0',
            'category' => 'required|string|max:50',
            'duration' => 'required|string|max:50',
            'is_weight_based' => 'required|boolean',
            'is_active' => 'required|boolean',
            'size' => 'nullable|string|max:10',
            'icon' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $service = Service::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $service,
                'message' => 'Layanan berhasil dibuat'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat layanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update service
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'member_price' => 'nullable|numeric|min:0',
            'category' => 'required|string|max:50',
            'duration' => 'required|string|max:50',
            'is_weight_based' => 'required|boolean',
            'is_active' => 'required|boolean',
            'size' => 'nullable|string|max:10',
            'icon' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $service = Service::findOrFail($id);
            $service->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $service,
                'message' => 'Layanan berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui layanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete service
     */
    public function destroy($id)
    {
        try {
            $service = Service::findOrFail($id);

            // Check if service is used in any orders
            if ($service->orderItems()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa menghapus layanan yang sudah digunakan dalam order'
                ], 400);
            }

            $service->delete();

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus layanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single service
     */
    public function show($id)
    {
        try {
            $service = Service::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $service
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan'
            ], 404);
        }
    }
}
