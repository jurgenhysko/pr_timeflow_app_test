<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
class ProductController extends BaseController
{

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::query();

            if($request->has('qs')) {
                $qs = $request->get('qs', '');
                $query->where(function($q) use ($qs) {
                    $q->where('name', 'like', "%$qs%")
                    ->orWhere('description', 'like', "%$qs%")
                    ->orWhere('price', 'like', "%$qs%")
                    ->orWhere('stock_quantity', 'like', "%$qs%")
                    ->orWhere('sku', 'like', "%$qs%");
                });
            }

            $query = $this->prepareSorting($query, Product::$sortable);

            $products = $this->paginate($query);
            return $this->success(ProductResource::collection($products));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = Product::create($request->validated());
            return $this->success(new ProductResource($product), 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            return $this->success(new ProductResource($product));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $product->update($request->validated());
            return $this->success(new ProductResource($product));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function destroy(string $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return $this->success(new ProductResource($product));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
