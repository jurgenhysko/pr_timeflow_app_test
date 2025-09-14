<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderResource;
class CustomerProfileResource extends JsonResource
{
    public function toArray($request)

    {
        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'email' => (string) $this->email,
            'phone' => (string) $this->phone,
            'address' => (string) $this->address,
            'orders' => OrderResource::collection($this->orders),
            'products' => $this->getProducts(),
            'favorite_product' => $this->favoriteProduct(),
        ];
    }
}