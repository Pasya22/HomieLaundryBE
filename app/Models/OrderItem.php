<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'service_id', 'quantity', 'unit_price', 'subtotal', 'notes', 'weight', 'custom_items'
    ];

    protected $casts = [
        'custom_items' => 'array',
        'weight' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Accessor untuk custom_items - ensure selalu array
    protected function customItems(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    return is_array($decoded) ? $decoded : [];
                }
                return is_array($value) ? $value : [];
            },
            set: fn ($value) => is_array($value) ? json_encode($value) : $value
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    protected static function boot()
    {
        parent::boot();

        // PERBAIKAN: Hitung subtotal berdasarkan jenis service
        static::creating(function ($item) {
            $item->subtotal = self::calculateSubtotal($item);
        });

        static::updating(function ($item) {
            $item->subtotal = self::calculateSubtotal($item);
        });
    }

    /**
     * Calculate subtotal based on service type
     */
    private static function calculateSubtotal($item): float
    {
        // Load service jika belum
        if (!$item->relationLoaded('service')) {
            $item->load('service');
        }

        $service = $item->service;

        // Jika service weight-based, hitung dari weight
        if ($service && $service->is_weight_based && $item->weight) {
            return round($item->weight * $item->unit_price, 2);
        }

        // Jika bukan weight-based, hitung dari quantity
        return round($item->quantity * $item->unit_price, 2);
    }

    /**
     * Get total items from custom_items
     */
    public function getTotalCustomItemsAttribute(): int
    {
        $items = $this->custom_items;

        if (!$items || !is_array($items)) {
            return 0;
        }

        return collect($items)->sum('quantity');
    }

    /**
     * Recalculate subtotal (can be called manually)
     */
    public function recalculateSubtotal(): void
    {
        $this->subtotal = self::calculateSubtotal($this);
        $this->save();
    }
}
