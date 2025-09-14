<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class OrderListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'customer_name' => (string) $this->customer_name,
            'total_amount' => (float) $this->total_amount,
            'status' => (string) $this->status,
            'order_date' => $this->order_date,
            'customer_name' => (string) $this->customer_name,
            'customer_email' => (string) $this->customer_email,
            'customer_phone' => (string) $this->customer_phone,
        ];
    }
}