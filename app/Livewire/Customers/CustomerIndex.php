<?php
// app/Livewire/Customers/CustomerIndex.php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;

class CustomerIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $name = '';
    public $phone = '';
    public $address = '';
    public $type = 'regular';
    public $deposit = 0;
    public $customerId;
    public $isEditing = false;
    public $showForm = false;

    protected $rules = [
        'name' => 'required|min:2|max:100',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'type' => 'required|in:regular,member',
        'deposit' => 'required_if:type,member|numeric|min:0'
    ];

    protected $messages = [
        'name.required' => 'Nama customer wajib diisi.',
        'name.min' => 'Nama minimal 2 karakter.',
        'deposit.required_if' => 'Deposit wajib diisi untuk member.',
    ];

    public function render()
    {
        $customers = Customer::when($this->search, function ($query) {
            return $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%');
        })->latest()->paginate(10);

        return view('livewire.customers.customer-index', compact('customers'));
    }

    public function create()
    {
        $this->resetInput();
        $this->showForm = true;
        $this->isEditing = false;
    }

    public function store()
    {
        $this->validate();

        try {
            $customerData = [
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
                'type' => $this->type,
            ];

            // Tambah data member jika type adalah member
            if ($this->type === 'member') {
                $customerData['deposit'] = $this->deposit;
                $customerData['balance'] = $this->deposit;
                $customerData['member_since'] = now();
                $customerData['member_expiry'] = now()->addYear();
            }

            Customer::create($customerData);

            $this->resetInput();
            $this->showForm = false;

            session()->flash('success', 'Customer berhasil ditambahkan!');
            $this->dispatch('customer-updated');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambah customer: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $this->customerId = $id;
            $this->name = $customer->name;
            $this->phone = $customer->phone;
            $this->address = $customer->address;
            $this->type = $customer->type;
            $this->deposit = $customer->deposit;
            $this->isEditing = true;
            $this->showForm = true;

        } catch (\Exception $e) {
            session()->flash('error', 'Customer tidak ditemukan!');
        }
    }

    public function update()
    {
        $this->validate();

        try {
            $customer = Customer::findOrFail($this->customerId);

            $customerData = [
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
                'type' => $this->type,
            ];

            // Update data member jika type adalah member
            if ($this->type === 'member') {
                $customerData['deposit'] = $this->deposit;
                // Update balance hanya jika deposit berubah
                if ($customer->deposit != $this->deposit) {
                    $customerData['balance'] = $this->deposit;
                }
                $customerData['member_since'] = $customer->member_since ?: now();
                $customerData['member_expiry'] = $customer->member_expiry ?: now()->addYear();
            } else {
                // Reset data member jika berubah dari member ke regular
                $customerData['deposit'] = 0;
                $customerData['balance'] = 0;
                $customerData['member_since'] = null;
                $customerData['member_expiry'] = null;
            }

            $customer->update($customerData);

            $this->resetInput();
            $this->showForm = false;

            session()->flash('success', 'Customer berhasil diupdate!');
            $this->dispatch('customer-updated');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal update customer: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            // Cek apakah customer punya order
            if ($customer->orders()->exists()) {
                session()->flash('error', 'Tidak bisa hapus customer yang sudah memiliki order!');
                return;
            }

            $customer->delete();
            session()->flash('success', 'Customer berhasil dihapus!');
            $this->dispatch('customer-updated');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal hapus customer: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        $this->resetInput();
        $this->showForm = false;
    }

    private function resetInput()
    {
        $this->reset(['name', 'phone', 'address', 'type', 'deposit', 'customerId']);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Helper untuk realtime events
    public function getListeners()
    {
        return [
            'customer-updated' => '$refresh',
        ];
    }

    // Helper method untuk status member
    public function getMemberStatus($customer)
    {
        if ($customer->type === 'member' && $customer->member_expiry && $customer->member_expiry->isFuture()) {
            return 'Member - Exp: ' . $customer->member_expiry->format('d/m/Y');
        }
        return 'Regular';
    }

    // Helper method untuk badge color
    public function getMemberBadge($customer)
    {
        if ($customer->type === 'member' && $customer->member_expiry && $customer->member_expiry->isFuture()) {
            return 'bg-green-100 text-green-800';
        }
        return 'bg-gray-100 text-gray-800';
    }
}
