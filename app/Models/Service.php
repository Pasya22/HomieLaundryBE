<?php
// app/Models/Service.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'size',
        'duration',
        'price',
        'member_price',
        'description',
        'icon',
        'is_active',
        'is_weight_based',
        'price_per_kg',
        'member_price_per_kg'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_weight_based' => 'boolean',
        'price' => 'decimal:2',
        'member_price' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'member_price_per_kg' => 'decimal:2',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get price berdasarkan member status dan tipe pricing
     *
     * @param bool $isMember
     * @return float
     */
    public function getPrice($isMember = false)
    {
        if ($this->is_weight_based) {
            // Untuk layanan kiloan
            if ($isMember && $this->member_price_per_kg) {
                return $this->member_price_per_kg;
            }
            return $this->price_per_kg ?? $this->price;
        } else {
            // Untuk layanan satuan
            if ($isMember && $this->member_price) {
                return $this->member_price;
            }
            return $this->price;
        }
    }

    /**
     * Calculate subtotal untuk item ini
     *
     * @param float $weight (untuk kiloan)
     * @param int $quantity (untuk satuan)
     * @param bool $isMember
     * @return float
     */
    public function calculateSubtotal($weight = null, $quantity = 1, $isMember = false)
    {
        $unitPrice = $this->getPrice($isMember);

        if ($this->is_weight_based) {
            return $unitPrice * ($weight ?? 1.0);
        } else {
            return $unitPrice * ($quantity ?? 1);
        }
    }

    /**
     * Get display text untuk pricing type
     */
    public function getPricingTypeText()
    {
        return $this->is_weight_based ? 'Per Kg' : 'Per Piece';
    }

    /**
     * Get unit text
     */
    public function getUnitText()
    {
        return $this->is_weight_based ? 'kg' : 'pcs';
    }

    /**
     * Helper methods untuk icons
     */
    public function getIconClass()
    {
        if ($this->icon) {
            return $this->icon;
        }

        $icons = [
            'REGULER' => 'fas fa-clock',
            'EXPRESS' => 'fas fa-bolt',
            'EKL_EXPRESS' => 'fas fa-rocket',
            'EKL_REGULER' => 'fas fa-truck',
            'PAKAIAN' => 'fas fa-tshirt',
            'SELIMUT' => 'fas fa-bed',
            'BED_COVER' => 'fas fa-blanket',
            'JAS' => 'fas fa-user-tie',
            'TAS' => 'fas fa-briefcase',
            'SEPATU' => 'fas fa-shoe-prints',
            'KARPET' => 'fas fa-square',
            'GORDEN' => 'fas fa-columns'
        ];

        return $icons[$this->category] ?? $icons[$this->name] ?? 'fas fa-cog';
    }

    public function getCategoryBadge()
    {
        $badges = [
            'REGULER' => 'bg-blue-100 text-blue-800',
            'EXPRESS' => 'bg-green-100 text-green-800',
            'EKL_EXPRESS' => 'bg-purple-100 text-purple-800',
            'EKL_REGULER' => 'bg-orange-100 text-orange-800',
            'PAKAIAN' => 'bg-indigo-100 text-indigo-800',
            'SELIMUT' => 'bg-pink-100 text-pink-800',
            'BED_COVER' => 'bg-teal-100 text-teal-800',
            'JAS' => 'bg-gray-800 text-white',
            'TAS' => 'bg-yellow-100 text-yellow-800',
            'SEPATU' => 'bg-red-100 text-red-800'
        ];

        return $badges[$this->category] ?? $badges[$this->name] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Scope untuk layanan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk layanan kiloan
     */
    public function scopeWeightBased($query)
    {
        return $query->where('is_weight_based', true);
    }

    /**
     * Scope untuk layanan satuan
     */
    public function scopePieceBased($query)
    {
        return $query->where('is_weight_based', false);
    }
}
