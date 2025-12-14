<?php
// app/Livewire/Services/ServiceIndex.php

namespace App\Livewire\Services;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceIndex extends Component
{
    use WithPagination;

    public $search           = '';
    public $selectedCategory = '';
    public $categories       = [];

    // Form properties
    public $name        = '';
    public $category    = '';
    public $size        = '';
    public $duration    = '';
    public $price       = '';
    public $description = '';
    public $icon        = '';
    public $serviceId;
    public $isEditing = false;
    public $showForm  = false;

    protected $rules = [
        'name'        => 'required|min:2|max:100',
        'category'    => 'required',
        'duration'    => 'required',
        'price'       => 'required|numeric|min:0',
        'description' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $this->categories = Service::distinct()->pluck('category')->toArray();
    }

    public function render()
    {
        $services = Service::when($this->search, function ($query) {
            return $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('category', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%');
        })
            ->when($this->selectedCategory, function ($query) {
                return $query->where('category', $this->selectedCategory);
            })
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('price')
            ->paginate(5);

        $categoryCounts = Service::where('is_active', true)
            ->groupBy('category')
            ->selectRaw('category, count(*) as count')
            ->pluck('count', 'category');

        return view('livewire.services.service-index', compact('services', 'categoryCounts'));
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetInput();
        $this->showForm  = true;
        $this->isEditing = false;
    }

    public function store()
    {
        $this->validate();

        try {
            Service::create([
                'name'        => $this->name,
                'category'    => $this->category,
                'size'        => $this->size,
                'duration'    => $this->duration,
                'price'       => $this->price,
                'description' => $this->description,
                'icon'        => $this->icon,
            ]);

            $this->resetInput();
            $this->showForm = false;

            // Clear any previous errors
            $this->resetErrorBag();

            session()->flash('success', 'Layanan berhasil ditambahkan!');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambah layanan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $service           = Service::findOrFail($id);
            $this->serviceId   = $id;
            $this->name        = $service->name;
            $this->category    = $service->category;
            $this->size        = $service->size;
            $this->duration    = $service->duration;
            $this->price       = $service->price;
            $this->description = $service->description;
            $this->icon        = $service->icon;
            $this->isEditing   = true;
            $this->showForm    = true;

        } catch (\Exception $e) {
            session()->flash('error', 'Layanan tidak ditemukan!');
        }
    }

    public function update()
    {
        $this->validate();

        try {
            $service = Service::findOrFail($this->serviceId);
            $service->update([
                'name'        => $this->name,
                'category'    => $this->category,
                'size'        => $this->size,
                'duration'    => $this->duration,
                'price'       => $this->price,
                'description' => $this->description,
                'icon'        => $this->icon,
            ]);

            $this->resetInput();
            $this->showForm = false;

            // Clear any previous errors
            $this->resetErrorBag();

            session()->flash('success', 'Layanan berhasil diupdate!');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal update layanan: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $service = Service::findOrFail($id);

            // Soft delete by setting inactive
            $service->update(['is_active' => false]);

            session()->flash('success', 'Layanan berhasil dihapus!');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal hapus layanan: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        $this->resetInput();
        $this->showForm = false;
        $this->resetErrorBag();
    }

    private function resetInput()
    {
        $this->reset(['name', 'category', 'size', 'duration', 'price', 'description', 'icon', 'serviceId']);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Helper methods for form options
    public function getDurations()
    {
        return [
            '4JAM'  => '4 Jam',
            '6JAM'  => '6 Jam',
            '12JAM' => '12 Jam',
            '1HARI' => '1 Hari',
            '2HARI' => '2 Hari',
            '3HARI' => '3 Hari',
        ];
    }

    public function getSizes()
    {
        return [
            ''           => 'Tidak Ada Ukuran',
            'SMALL'      => 'Small',
            'MEDIUM'     => 'Medium',
            'KING'       => 'King',
            'SUPER_KING' => 'Super King',
        ];
    }

    public function getIcons()
    {
        return [
            'fas fa-tshirt'      => 'Pakaian',
            'fas fa-bed'         => 'Selimut',
            'fas fa-blanket'     => 'Bed Cover',
            'fas fa-user-tie'    => 'Jas',
            'fas fa-briefcase'   => 'Tas',
            'fas fa-shoe-prints' => 'Sepatu',
            'fas fa-square'      => 'Karpet',
            'fas fa-columns'     => 'Gorden',
            'fas fa-clock'       => 'Reguler',
            'fas fa-bolt'        => 'Express',
            'fas fa-rocket'      => 'Ekspress Kilat',
            'fas fa-truck'       => 'Reguler Kilat',
        ];
    }
}
