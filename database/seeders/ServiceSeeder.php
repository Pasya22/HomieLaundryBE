<?php
// database/seeders/ServiceSeeder.php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            // ========== CUCI KILOAN (Weight-Based) ==========
            [
                'name' => 'REGULER',
                'category' => 'PAKAIAN',
                'size' => null,
                'duration' => '2HARI',
                'is_weight_based' => true,
                'price' => 7000, // Harga per kg
                'price_per_kg' => 7000,
                'member_price' => 6000,
                'member_price_per_kg' => 6000,
                'description' => 'Cuci + Setrika pakaian reguler per kilogram',
                'icon' => 'fas fa-tshirt',
                'is_active' => true,
            ],
            [
                'name' => 'EXPRESS',
                'category' => 'PAKAIAN',
                'size' => null,
                'duration' => '1HARI',
                'is_weight_based' => true,
                'price' => 10000,
                'price_per_kg' => 10000,
                'member_price' => 9000,
                'member_price_per_kg' => 9000,
                'description' => 'Cuci + Setrika pakaian express per kilogram',
                'icon' => 'fas fa-bolt',
                'is_active' => true,
            ],
            [
                'name' => 'EKL_REGULER',
                'category' => 'PAKAIAN',
                'size' => null,
                'duration' => '2HARI',
                'is_weight_based' => true,
                'price' => 5000,
                'price_per_kg' => 5000,
                'member_price' => 4500,
                'member_price_per_kg' => 4500,
                'description' => 'Cuci kering lipat (ekonomis) per kilogram',
                'icon' => 'fas fa-truck',
                'is_active' => true,
            ],
            [
                'name' => 'EKL_EXPRESS',
                'category' => 'PAKAIAN',
                'size' => null,
                'duration' => '6JAM',
                'is_weight_based' => true,
                'price' => 8000,
                'price_per_kg' => 8000,
                'member_price' => 7000,
                'member_price_per_kg' => 7000,
                'description' => 'Cuci kering lipat express per kilogram',
                'icon' => 'fas fa-rocket',
                'is_active' => true,
            ],

            // ========== CUCI SATUAN (Piece-Based) ==========

            // JAS & FORMAL
            [
                'name' => 'JAS',
                'category' => 'JAS',
                'size' => null,
                'duration' => '3HARI',
                'is_weight_based' => false,
                'price' => 25000, // Per piece
                'member_price' => 22000,
                'price_per_kg' => null,
                'member_price_per_kg' => null,
                'description' => 'Cuci dry clean jas/blazer per piece',
                'icon' => 'fas fa-user-tie',
                'is_active' => true,
            ],
            [
                'name' => 'KEMEJA',
                'category' => 'PAKAIAN',
                'size' => null,
                'duration' => '2HARI',
                'is_weight_based' => false,
                'price' => 8000,
                'member_price' => 7000,
                'price_per_kg' => null,
                'member_price_per_kg' => null,
                'description' => 'Cuci + setrika kemeja per piece',
                'icon' => 'fas fa-tshirt',
                'is_active' => true,
            ],

            // SELIMUT & BED COVER
            [
                'name' => 'SELIMUT_SMALL',
                'category' => 'SELIMUT',
                'size' => 'SMALL',
                'duration' => '2HARI',
                'is_weight_based' => false,
                'price' => 15000,
                'member_price' => 13000,
                'description' => 'Cuci selimut ukuran kecil/single',
                'icon' => 'fas fa-bed',
                'is_active' => true,
            ],
            [
                'name' => 'SELIMUT_MEDIUM',
                'category' => 'SELIMUT',
                'size' => 'MEDIUM',
                'duration' => '2HARI',
                'is_weight_based' => false,
                'price' => 20000,
                'member_price' => 18000,
                'description' => 'Cuci selimut ukuran sedang/double',
                'icon' => 'fas fa-bed',
                'is_active' => true,
            ],
            [
                'name' => 'BED_COVER_KING',
                'category' => 'BED_COVER',
                'size' => 'KING',
                'duration' => '3HARI',
                'is_weight_based' => false,
                'price' => 35000,
                'member_price' => 30000,
                'description' => 'Cuci bed cover ukuran king',
                'icon' => 'fas fa-blanket',
                'is_active' => true,
            ],
            [
                'name' => 'BED_COVER_SUPER_KING',
                'category' => 'BED_COVER',
                'size' => 'SUPER_KING',
                'duration' => '3HARI',
                'is_weight_based' => false,
                'price' => 45000,
                'member_price' => 40000,
                'description' => 'Cuci bed cover ukuran super king',
                'icon' => 'fas fa-blanket',
                'is_active' => true,
            ],

            // TAS & SEPATU
            [
                'name' => 'TAS_KECIL',
                'category' => 'TAS',
                'size' => 'SMALL',
                'duration' => '3HARI',
                'is_weight_based' => false,
                'price' => 20000,
                'member_price' => 18000,
                'description' => 'Cuci tas kecil/clutch',
                'icon' => 'fas fa-briefcase',
                'is_active' => true,
            ],
            [
                'name' => 'TAS_BESAR',
                'category' => 'TAS',
                'size' => 'LARGE',
                'duration' => '3HARI',
                'is_weight_based' => false,
                'price' => 35000,
                'member_price' => 30000,
                'description' => 'Cuci tas besar/backpack',
                'icon' => 'fas fa-briefcase',
                'is_active' => true,
            ],
            [
                'name' => 'SEPATU_SNEAKERS',
                'category' => 'SEPATU',
                'size' => null,
                'duration' => '2HARI',
                'is_weight_based' => false,
                'price' => 25000,
                'member_price' => 22000,
                'description' => 'Cuci sepatu sneakers',
                'icon' => 'fas fa-shoe-prints',
                'is_active' => true,
            ],
            [
                'name' => 'SEPATU_FORMAL',
                'category' => 'SEPATU',
                'size' => null,
                'duration' => '3HARI',
                'is_weight_based' => false,
                'price' => 30000,
                'member_price' => 27000,
                'description' => 'Cuci + poles sepatu formal/pantofel',
                'icon' => 'fas fa-shoe-prints',
                'is_active' => true,
            ],

            // KARPET & GORDEN
            [
                'name' => 'KARPET_KECIL',
                'category' => 'KARPET',
                'size' => 'SMALL',
                'duration' => '3HARI',
                'is_weight_based' => false,
                'price' => 25000,
                'member_price' => 22000,
                'description' => 'Cuci karpet ukuran kecil (max 1x2m)',
                'icon' => 'fas fa-square',
                'is_active' => true,
            ],
            [
                'name' => 'KARPET_BESAR',
                'category' => 'KARPET',
                'size' => 'LARGE',
                'duration' => '4HARI',
                'is_weight_based' => false,
                'price' => 50000,
                'member_price' => 45000,
                'description' => 'Cuci karpet ukuran besar (2x3m keatas)',
                'icon' => 'fas fa-square',
                'is_active' => true,
            ],
            [
                'name' => 'GORDEN_PER_METER',
                'category' => 'GORDEN',
                'size' => null,
                'duration' => '3HARI',
                'is_weight_based' => false,
                'price' => 15000,
                'member_price' => 13000,
                'description' => 'Cuci gorden per meter persegi',
                'icon' => 'fas fa-columns',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
