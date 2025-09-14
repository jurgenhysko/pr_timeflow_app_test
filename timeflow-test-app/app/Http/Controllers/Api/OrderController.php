<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use Carbon\Carbon;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderListResource;
class OrderController extends BaseController
{

    public function index(Request $request): JsonResponse
    {
        try {   
            $query = Order::query()->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('order_product', 'orders.id', '=', 'order_product.order_id')
            ->leftJoin('products', 'order_product.product_id', '=', 'products.id');

            if($request->has('qs')) {
                $qs = $request->get('qs', '');
                $query->where(function($q) use ($qs) {
                    $q->where('orders.id', 'like', "%$qs%")
                    ->orWhere('orders.status', 'like', "%$qs%")
                    ->orWhere('customers.name', 'like', "%$qs%")
                    ->orWhere('customers.email', 'like', "%$qs%")
                    ->orWhere('customers.phone', 'like', "%$qs%")
                    ->orWhere('customers.address', 'like', "%$qs%")
                    ->orWhere('products.name', 'like', "%$qs%")
                    ->orWhere('products.description', 'like', "%$qs%")
                    ->orWhere('products.price', 'like', "%$qs%")
                    ->orWhere('products.stock_quantity', 'like', "%$qs%")
                    ->orWhere('products.sku', 'like', "%$qs%");
                });
            }
            
            if ($request->has('status')) {
                $query->where('orders.status', $request->status);
            }
            
            $dbSortBy = Order::$sortFieldMap[$request->get('sort_by', 'order_date')] ?? 'orders.order_date';
            $query = $this->prepareSorting($query, Order::$sortable, $dbSortBy);
            
            $query->groupBy('orders.id');

            $query->select([
                'orders.id',
                DB::raw('ANY_VALUE(orders.status) as status'),
                DB::raw('ANY_VALUE(orders.order_date) as order_date'),
                DB::raw('ANY_VALUE(orders.total_amount) as total_amount'),
                DB::raw('ANY_VALUE(customers.name) as customer_name'),
                DB::raw('ANY_VALUE(customers.email) as customer_email'),
                DB::raw('ANY_VALUE(customers.phone) as customer_phone'),
                DB::raw('ANY_VALUE(customers.address) as customer_address'),
            ]);

            $orders = $this->paginate($query);
            return $this->success(OrderListResource::collection($orders));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $orderProducts = [];

            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $price = $product->price;
                $quantity = $productData['quantity'];

                if ($product->stock_quantity < $quantity) {
                    throw new \Exception("Insufficient stock for product {$product->name}");
                }

                $totalAmount += $price * $quantity;
                $orderProducts[$productData['product_id']] = [
                    'quantity' => $quantity,
                    'price' => $price
                ];
            }
            
            $order = Order::create([
                'customer_id' => $validated['customer_id'],
                'total_amount' => $totalAmount,
                'status' => 'processing',
                'order_date' => now(),
            ]);

            $order->products()->attach($orderProducts);

            $order->load(['products', 'customer']);

            DB::commit();
            return $this->success(new OrderResource($order), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }


    public function show(string $id): JsonResponse
    {
        try {
            $order = Order::with(['customer', 'products'])->findOrFail($id);
            return $this->success(new OrderResource($order));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function update(UpdateOrderRequest $request, string $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
        $order->update($request->validated());
        $order->load(['customer', 'products']);
            return $this->success(new OrderResource($order));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function destroy(string $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
        $order->delete();
            return $this->success(new OrderResource($order));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function dailyOrders(): JsonResponse {
        $startDate = request()->get('start_date') ?? now()->subDays(30);
        $endDate = request()->get('end_date') ?? now(); 

        $orders = Order::where('order_date', '>=', $startDate)
            ->where('order_date', '<=', $endDate)
            ->get();
        $dates = Carbon::parse($startDate)->daysUntil($endDate)->toArray();
        foreach ($dates as $date) { 
            $ordersOfDate = $orders->where('order_date', $date->format('Y-m-d'));
            // $ordersOfDate = $orders->filter(function($order) use ($date) {
            //     return $order->order_date->format('Y-m-d') == $date->format('Y-m-d');
            // });
            $result[$date->format('Y-m-d')] = $ordersOfDate->count();
        }
        return $this->success(new OrderResource($result));

    }

    public function monthlyRevenue(): JsonResponse {
        $startDate = request()->get('start_date') ?? now()->subDays(30);
        $endDate = request()->get('end_date') ?? now(); 

        $orders = Order::where('order_date', '>=', $startDate)
            ->where('order_date', '<=', $endDate)
            ->groupBy('order_date')
            ->select([
                'order_date',
                DB::raw('SUM(total_amount) as total_amount')
            ])
            ->get();
        return $this->success(OrderResource::collection($orders));
    }
}
