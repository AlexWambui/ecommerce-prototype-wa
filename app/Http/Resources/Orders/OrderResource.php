<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'order_number' => $this->order_number,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'delivery_location' => $this->delivery_location,
            'delivery_area' => $this->delivery_area,
            'delivery_address' => $this->delivery_address,
            'delivery_cost' => $this->delivery_cost,
            'subtotal' => $this->subtotal,
            'total_selling_price' => $this->total_selling_price,
            'amount_paid' => $this->amount_paid,
            'payment_status' => $this->payment_status,
            'order_status' => $this->order_status,
            'notes' => $this->notes,
            'sold_at' => $this->sold_at->setTimezone('Africa/Nairobi')->format('d-m-Y H:i'),
            'payments' => $this->payments,
            'order_items' => $this->orderItems,
        ];
    }
}
