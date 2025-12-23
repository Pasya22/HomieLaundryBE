<?php
// app/Http/Controllers/Api/CustomerController.php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $perPage = $request->per_page ?? 10;
        $customers = $query->latest()->paginate($perPage);

        return CustomerResource::collection($customers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'type' => 'required|in:regular,member',
            'deposit' => 'required_if:type,member|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customerData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'type' => $request->type,
        ];

        if ($request->type === 'member') {
            $customerData['deposit'] = $request->deposit;
            $customerData['balance'] = $request->deposit;
            $customerData['member_since'] = now();
            $customerData['member_expiry'] = now()->addYear();
        }

        $customer = Customer::create($customerData);

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil ditambahkan',
            'data' => new CustomerResource($customer)
        ], 201);
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return new CustomerResource($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'type' => 'required|in:regular,member',
            'deposit' => 'required_if:type,member|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customerData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'type' => $request->type,
        ];

        if ($request->type === 'member') {
            $customerData['deposit'] = $request->deposit;
            if ($customer->deposit != $request->deposit) {
                $customerData['balance'] = $request->deposit;
            }
            $customerData['member_since'] = $customer->member_since ?: now();
            $customerData['member_expiry'] = $customer->member_expiry ?: now()->addYear();
        } else {
            $customerData['deposit'] = 0;
            $customerData['balance'] = 0;
            $customerData['member_since'] = null;
            $customerData['member_expiry'] = null;
        }

        $customer->update($customerData);

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil diupdate',
            'data' => new CustomerResource($customer)
        ]);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->orders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa hapus customer yang sudah memiliki order'
            ], 400);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil dihapus'
        ]);
    }
}
