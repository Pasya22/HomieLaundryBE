<?php
// app/Http/Resources/CustomerResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'type' => $this->type,
            'deposit' => (int) $this->deposit,
            'balance' => (int) $this->balance,
            'member_since' => $this->member_since?->format('Y-m-d'),
            'member_expiry' => $this->member_expiry?->format('Y-m-d'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_formatted' => $this->created_at->format('d/m/Y'),
            'can_delete' => !$this->orders()->exists(),
            'member_status' => $this->type === 'member' &&
                $this->member_expiry &&
                $this->member_expiry->isFuture() ? 'active' : 'inactive'
        ];
    }
}
