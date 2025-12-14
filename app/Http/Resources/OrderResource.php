<?php
// app/Http/Resources/OrderResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'order_number' => $this->order_number,
            'customer'     => [
                'id'   => $this->customer->id,
                'name' => $this->customer->name,
            ],
            'status'       => $this->status,
            'total_amount' => (int) $this->total_amount,
            'created_at'   => $this->created_at->format('Y-m-d H:i:s'),
            'status_text'  => $this->getStatusText(),
            'status_badge' => $this->getStatusBadge(),
        ];
    }

    protected function getStatusText(): string
    {
        $texts = [
            'request'   => 'Baru',
            'washing'   => 'Cuci',
            'drying'    => 'Kering',
            'ironing'   => 'Setrika',
            'ready'     => 'Siap',
            'completed' => 'Selesai',
        ];

        return $texts[$this->status] ?? 'Baru';
    }

    protected function getStatusBadge(): string
    {
        $badges = [
            'request'   => 'bg-blue-100 text-blue-800',
            'washing'   => 'bg-yellow-100 text-yellow-800',
            'drying'    => 'bg-orange-100 text-orange-800',
            'ironing'   => 'bg-purple-100 text-purple-800',
            'ready'     => 'bg-green-100 text-green-800',
            'completed' => 'bg-green-100 text-green-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}
