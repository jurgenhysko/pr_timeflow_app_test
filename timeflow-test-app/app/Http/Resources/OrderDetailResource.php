<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray($request)

    {
        return [
            'id' => (int) $this->id,
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'product' => new ProductResource($this->product),
            'name' => (string) $this->product->name,
            'description' => (string) $this->product->description,
            'stock_quantity' => (int) $this->product->stock_quantity,
            'sku' => (string) $this->product->sku,
        ];
    }
}