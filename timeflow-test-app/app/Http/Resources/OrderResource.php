<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'total_amount' => (float) $this->total_amount,
            'status' => (string) $this->status,
            'order_date' => $this->order_date,
            'products' => ProductResource::collection($this->products),
            'customer' => new CustomerResource($this->customer),
            'details' => OrderDetailResource::collection($this->details),
        ];
    }
}